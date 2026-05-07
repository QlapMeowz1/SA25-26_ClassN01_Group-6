<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TournamentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $myTournaments = $user->tournaments()
                              ->where('status', '!=', 'completed')
                              ->orderBy('start_date')
                              ->paginate(10);

        $upcomingTournaments = Tournament::where('status', 'upcoming')
                                        ->orderBy('start_date')
                                        ->paginate(10)
                                        ->map(fn($t) => $this->decorateTournament($t))
                                        ->concat($this->buildSampleTournaments());

        // Get featured tournament (next upcoming)
        $featuredTournament = Tournament::where('status', 'upcoming')
                                       ->orderBy('start_date')
                                       ->first();
        
        if ($featuredTournament) {
            $featuredTournament = $this->decorateTournament($featuredTournament);
        }

        // Add featured from samples if no real tournament
        if (!$featuredTournament) {
            $samples = $this->buildSampleTournaments();
            $featuredTournament = $samples->first();
        }

        return view('tournaments.index', compact('upcomingTournaments', 'myTournaments', 'featuredTournament'));
    }

    private function decorateTournament($tournament)
    {
        $tournament->arena_countdown = $this->formatCountdown($tournament->start_date);
        $tournament->arena_slots_filled = $tournament->tournamentParticipants()->count();
        $tournament->arena_slots_remaining = max(0, $tournament->max_participants - $tournament->arena_slots_filled);
        $tournament->arena_prize_display = $this->formatPrizePool($tournament->prize_pool);
        $tournament->arena_registration_deadline = $tournament->start_date->subDays(1)->format('M d, g\A');

        return $tournament;
    }

    private function buildSampleTournaments()
    {
        return collect([
            (object)[
                'id' => 'sample-1',
                'name' => 'Spring Championship 2026',
                'description' => 'The premier spring championship with top players competing for glory and prizes.',
                'organizer_id' => null,
                'organizer' => (object)['name' => 'BadNet Events'],
                'start_date' => Carbon::now()->addDays(14),
                'end_date' => Carbon::now()->addDays(16),
                'max_participants' => 16,
                'status' => 'upcoming',
                'prize_pool' => 5000,
                'prize_details' => '5000 coins + Trophy badge',
                'banner_color' => 'spring',
                'tournamentParticipants' => collect([
                    (object)['id' => 1], (object)['id' => 2], (object)['id' => 3], 
                    (object)['id' => 4], (object)['id' => 5], (object)['id' => 6],
                    (object)['id' => 7], (object)['id' => 8], (object)['id' => 9],
                    (object)['id' => 10], (object)['id' => 11], (object)['id' => 12],
                ]),
                'arena_countdown' => $this->formatCountdown(Carbon::now()->addDays(14)),
                'arena_slots_filled' => 12,
                'arena_slots_remaining' => 4,
                'arena_prize_display' => '5000 coins',
                'arena_registration_deadline' => Carbon::now()->addDays(13)->format('M d, g\A'),
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-2',
                'name' => 'Weekly Casual Cup',
                'description' => 'A fun, laid-back tournament for casual players looking to get some friendly competition.',
                'organizer_id' => null,
                'organizer' => (object)['name' => 'Community Organizers'],
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(7),
                'max_participants' => 8,
                'status' => 'upcoming',
                'prize_pool' => 2000,
                'prize_details' => '2000 coins',
                'banner_color' => 'casual',
                'tournamentParticipants' => collect([
                    (object)['id' => 1], (object)['id' => 2], (object)['id' => 3],
                    (object)['id' => 4], (object)['id' => 5],
                ]),
                'arena_countdown' => $this->formatCountdown(Carbon::now()->addDays(7)),
                'arena_slots_filled' => 5,
                'arena_slots_remaining' => 3,
                'arena_prize_display' => '2000 coins',
                'arena_registration_deadline' => Carbon::now()->addDays(6)->format('M d, g\A'),
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-3',
                'name' => 'District Open Tournament',
                'description' => 'A larger, more competitive regional tournament with opportunities for recognition.',
                'organizer_id' => null,
                'organizer' => (object)['name' => 'District Sports Council'],
                'start_date' => Carbon::now()->addDays(21),
                'end_date' => Carbon::now()->addDays(23),
                'max_participants' => 32,
                'status' => 'upcoming',
                'prize_pool' => 10000,
                'prize_details' => '10000 coins + Champion role',
                'banner_color' => 'district',
                'tournamentParticipants' => collect([
                    (object)['id' => 1], (object)['id' => 2], (object)['id' => 3],
                    (object)['id' => 4], (object)['id' => 5], (object)['id' => 6],
                    (object)['id' => 7], (object)['id' => 8],
                ]),
                'arena_countdown' => $this->formatCountdown(Carbon::now()->addDays(21)),
                'arena_slots_filled' => 8,
                'arena_slots_remaining' => 24,
                'arena_prize_display' => '10000 coins',
                'arena_registration_deadline' => Carbon::now()->addDays(20)->format('M d, g\A'),
                'is_sample' => true,
            ],
        ]);
    }

    private function formatCountdown($date)
    {
        $now = Carbon::now();
        $diff = $date->diffInDays($now, false);

        if ($diff < 0) {
            $days = abs($diff);
            $hours = $date->diffInHours($now, false) % 24;
            if ($hours < 0) $hours += 24;
            return $days . 'd ' . $hours . 'h left';
        }

        return 'Happening now';
    }

    private function formatPrizePool($pool)
    {
        if ($pool >= 1000) {
            return ($pool / 1000) . 'K coins';
        }
        return $pool . ' coins';
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
