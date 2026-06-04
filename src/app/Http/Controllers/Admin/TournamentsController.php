<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TournamentsController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::withCount('tournamentParticipants')
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'upcoming' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
            ->orderBy('start_date')
            ->get()
            ->map(function (Tournament $tournament) {
                $players = (int) $tournament->tournament_participants_count;
                $capacity = max(1, (int) ($tournament->max_participants ?: $players ?: 1));

                return [
                    'id' => 'tournament-' . $tournament->id,
                    'name' => $tournament->name,
                    'tag' => Str::headline($tournament->status ?: 'open'),
                    'venue' => $tournament->description ? Str::limit($tournament->description, 42) : 'Main badminton hall',
                    'dates' => $this->dateRange($tournament),
                    'players' => $players,
                    'prize' => '₹' . number_format((float) ($tournament->prize_pool ?? 0)),
                    'status' => $this->displayStatus($tournament->status),
                    'progress' => min(100, (int) round(($players / $capacity) * 100)),
                ];
            })
            ->all();

        if ($tournaments === []) {
            $tournaments = AdminMockData::tournaments();
        }

        return view('admin.tournaments', compact('tournaments'));
    }

    public function create()
    {
        return view('admin.tournaments-create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['required', 'integer', 'min:2', 'max:10000'],
            'status' => ['required', Rule::in(['upcoming', 'in_progress', 'completed'])],
            'prize_pool' => ['nullable', 'numeric', 'min:0'],
        ]);

        Tournament::create([
            ...$data,
            'organizer_id' => auth()->id(),
            'prize_pool' => $data['prize_pool'] ?? 0,
        ]);

        return redirect()->route('admin.tournaments')->with('success', 'Đã tạo tournament mới.');
    }

    private function dateRange(Tournament $tournament): string
    {
        $start = $tournament->start_date?->format('M d, Y') ?? 'TBD';
        $end = $tournament->end_date?->format('M d, Y') ?? 'TBD';

        return "{$start} - {$end}";
    }

    private function displayStatus(?string $status): string
    {
        return match ($status) {
            'in_progress' => 'Ongoing',
            'upcoming' => 'Upcoming',
            'completed' => 'Completed',
            default => Str::headline($status ?: 'Upcoming'),
        };
    }
}
