<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

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
            ->with('organizer')
            ->orderBy('start_date')
            ->get()
            ->map(fn ($tournament) => $this->decorateTournament($tournament, false));

        $myIds = $myTournaments->pluck('id')->filter()->all();

        $upcomingTournaments = Tournament::with('organizer')
            ->where('status', 'upcoming')
            ->when(!empty($myIds), fn ($query) => $query->whereNotIn('id', $myIds))
            ->orderBy('start_date')
            ->get()
            ->map(fn ($tournament) => $this->decorateTournament($tournament, false));

        if ($upcomingTournaments->isEmpty() && $myTournaments->isEmpty()) {
            $samples = $this->sampleTournaments(false);
            $myTournaments = $samples->take(2)->values();
            $upcomingTournaments = $samples->skip(2)->values();
        }

        $featuredTournament = $upcomingTournaments->first() ?? $myTournaments->first() ?? $this->sampleTournaments(true)->first();

        if (!$featuredTournament) {
            $featuredTournament = $this->decorateTournament(
                Tournament::make([
                    'id' => 'featured-tournament',
                    'name' => 'Spring Championship 2026',
                    'description' => 'A flagship season opener for elite tournament players.',
                    'status' => 'upcoming',
                    'start_date' => Carbon::now()->addWeeks(2),
                    'end_date' => Carbon::now()->addWeeks(2)->addDay(),
                    'max_participants' => 16,
                    'prize_pool' => 5000,
                ]),
                true
            );
        }

        $myTournaments = $myTournaments->take(10);
        $upcomingTournaments = $upcomingTournaments->take(10);

        return view('tournaments.index', compact('featuredTournament', 'upcomingTournaments', 'myTournaments'));
    }

    private function decorateTournament(Tournament $tournament, bool $featured = false)
    {
        $slotsFilled = $tournament->tournamentParticipants?->count() ?? ($tournament->participants_count ?? 0);
        $slotsTotal = $tournament->max_participants ?? 16;
        $deadline = $tournament->end_date ?? optional($tournament->start_date)->copy()->subHours(2);

        $tournament->slots_filled = $slotsFilled;
        $tournament->slots_total = $slotsTotal;
        $tournament->slots_percentage = $slotsTotal > 0 ? round(($slotsFilled / $slotsTotal) * 100) : 0;
        $tournament->countdown = $this->formatCountdown($tournament->start_date ?? now(), $featured);
        $tournament->time_until_deadline = $deadline ? $deadline->diffForHumans() : 'Soon';
        $tournament->prize_details = $this->formatPrizeDetails($tournament);
        $tournament->banner_color = $this->bannerColorForTournament($tournament);
        $tournament->tournament_type = $featured ? 'featured' : 'standard';

        return $tournament;
    }

    private function sampleTournaments(bool $featured = false)
    {
        $samples = collect([
            [
                'id' => 'sample-spring-championship-2026',
                'name' => 'Spring Championship 2026',
                'description' => 'The biggest circuit event of the spring season. Play for glory, ranking points, and the trophy badge.',
                'status' => 'upcoming',
                'start_date' => Carbon::create(2026, 5, 28, 15, 0, 0, 'Asia/Ho_Chi_Minh'),
                'end_date' => Carbon::create(2026, 5, 29, 20, 0, 0, 'Asia/Ho_Chi_Minh'),
                'max_participants' => 16,
                'prize_pool' => 5000,
                'banner_color' => '#f97316',
                'participant_count' => 12,
                'prize_details' => '5000 🪙 Coins',
                'organizer' => 'CourtKings',
            ],
            [
                'id' => 'sample-rookie-cup-2026',
                'name' => 'Rookie Cup 2026',
                'description' => 'A welcoming bracket for rising players looking for their first tournament run.',
                'status' => 'upcoming',
                'start_date' => Carbon::create(2026, 5, 21, 15, 0, 0, 'Asia/Ho_Chi_Minh'),
                'end_date' => Carbon::create(2026, 5, 22, 20, 0, 0, 'Asia/Ho_Chi_Minh'),
                'max_participants' => 8,
                'prize_pool' => 1000,
                'banner_color' => '#22c55e',
                'participant_count' => 3,
                'prize_details' => '1000 🪙 Coins',
                'organizer' => 'ShuttleKing',
            ],
            [
                'id' => 'sample-weekend-warrior-open',
                'name' => 'Weekend Warrior Open',
                'description' => 'Weekend competition for active community players.',
                'status' => 'upcoming',
                'start_date' => Carbon::create(2026, 5, 30, 9, 0, 0, 'Asia/Ho_Chi_Minh'),
                'end_date' => Carbon::create(2026, 5, 30, 22, 0, 0, 'Asia/Ho_Chi_Minh'),
                'max_participants' => 16,
                'prize_pool' => 2000,
                'banner_color' => '#3b82f6',
                'participant_count' => 5,
                'prize_details' => '2000 🪙 Coins',
                'organizer' => 'RacketMaster',
            ],
            [
                'id' => 'sample-city-smash-fest',
                'name' => 'City Smash Fest',
                'description' => 'Large city-wide event with mixed divisions and knockout finals.',
                'status' => 'upcoming',
                'start_date' => Carbon::create(2026, 6, 5, 9, 0, 0, 'Asia/Ho_Chi_Minh'),
                'end_date' => Carbon::create(2026, 6, 6, 20, 0, 0, 'Asia/Ho_Chi_Minh'),
                'max_participants' => 32,
                'prize_pool' => 3000,
                'banner_color' => '#8b5cf6',
                'participant_count' => 8,
                'prize_details' => '3000 🪙 Coins',
                'organizer' => 'NetNinjas',
            ],
            [
                'id' => 'sample-summer-slam-2026',
                'name' => 'Summer Slam 2026',
                'description' => 'Premier summer major with elite seeding and championship bracket.',
                'status' => 'upcoming',
                'start_date' => Carbon::create(2026, 6, 15, 10, 0, 0, 'Asia/Ho_Chi_Minh'),
                'end_date' => Carbon::create(2026, 6, 16, 21, 0, 0, 'Asia/Ho_Chi_Minh'),
                'max_participants' => 32,
                'prize_pool' => 10000,
                'banner_color' => '#ef4444',
                'participant_count' => 15,
                'prize_details' => '10000 🪙 Coins',
                'organizer' => 'SmashPro',
            ],
        ]);

        $decorated = $samples->map(function (array $sample) {
            $tournament = Tournament::make($sample);
            $tournament->id = $sample['id'];
            $tournament->name = $sample['name'];
            $tournament->description = $sample['description'];
            $tournament->status = $sample['status'];
            $tournament->start_date = $sample['start_date'];
            $tournament->end_date = $sample['end_date'];
            $tournament->max_participants = $sample['max_participants'];
            $tournament->prize_pool = $sample['prize_pool'];
            $tournament->banner_color = $sample['banner_color'];
            $tournament->prize_details = $sample['prize_details'];
            $tournament->organizer = (object) ['name' => $sample['organizer']];
            $tournament->tournamentParticipants = collect(range(1, $sample['participant_count']))->map(fn () => new \stdClass());

            return $this->decorateTournament($tournament, $sample['id'] === 'sample-spring-championship-2026');
        });

        return $featured ? $decorated->take(1) : $decorated;
    }

    private function formatCountdown(Carbon $date, bool $featured = false)
    {
        $minutes = now()->diffInMinutes($date, false);
        if ($minutes <= 0) {
            return $featured ? 'Starts now' : 'Starting soon';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($mins > 0) $parts[] = $mins . 'm';

        return 'Starts in ' . implode(' ', array_slice($parts, 0, 3));
    }

    private function formatPrizeDetails(Tournament $tournament)
    {
        if (!empty($tournament->prize_details)) {
            return $tournament->prize_details;
        }

        return number_format($tournament->prize_pool ?? 0) . ' 🪙 Coins';
    }

    private function bannerColorForTournament(Tournament $tournament)
    {
        return match (strtolower($tournament->name ?? '')) {
            'spring championship 2026' => '#f97316',
            'rookie cup 2026' => '#22c55e',
            'weekend warrior open' => '#3b82f6',
            'city smash fest' => '#8b5cf6',
            'summer slam 2026' => '#ef4444',
            default => '#6366f1',
        };
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
