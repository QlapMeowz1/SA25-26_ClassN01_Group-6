<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TournamentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $featuredTournament = Tournament::where('status', 'upcoming')
                                        ->orderBy('start_date')
                                        ->first();

        $upcomingTournaments = Tournament::where('status', 'upcoming')
                                        ->orderBy('start_date')
                                        ->paginate(10);
        $myTournaments = Auth::user()->tournaments()->paginate(10);

        return view('tournaments.index', compact('featuredTournament', 'upcomingTournaments', 'myTournaments'));
    }

    public function show(Tournament $tournament)
    {
        $participants = $tournament->tournamentParticipants()
                                   ->with('user')
                                   ->orderBy('position')
                                   ->paginate(20);

        return view('tournaments.show', compact('tournament', 'participants'));
    }

    public function create()
    {
        return view('tournaments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'max_participants' => 'required|integer|min:4|max:100',
            'prize_pool' => 'nullable|integer|min:0',
        ]);

        $tournament = Tournament::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'organizer_id' => Auth::id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'max_participants' => $validated['max_participants'],
            'prize_pool' => $validated['prize_pool'] ?? 0,
        ]);

        TournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('tournaments.show', $tournament->id)->with('success', 'Tournament created!');
    }

    public function join(Tournament $tournament)
    {
        $user = Auth::user();

        if ($tournament->isFull()) {
            return back()->with('error', 'Tournament is full!');
        }

        if ($tournament->hasParticipant($user->id)) {
            return back()->with('error', 'You are already registered!');
        }

        TournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Registered for tournament!');
    }

    public function leave(Tournament $tournament)
    {
        $user = Auth::user();

        if ($tournament->organizer_id === $user->id) {
            return back()->with('error', 'Organizer cannot leave!');
        }

        TournamentParticipant::where('tournament_id', $tournament->id)
                              ->where('user_id', $user->id)
                              ->delete();

        return back()->with('success', 'Unregistered from tournament!');
    }
}
