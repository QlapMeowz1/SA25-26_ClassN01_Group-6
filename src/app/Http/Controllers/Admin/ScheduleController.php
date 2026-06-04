<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->query('date', now()->toDateString());
        $month = Carbon::parse($selectedDate)->startOfMonth();
        $matches = GameMatch::with(['player1', 'player2'])
            ->whereDate('match_date', $selectedDate)
            ->orderBy('match_date')
            ->get()
            ->map(fn (GameMatch $match) => [
                'id' => 'M-' . str_pad((string) $match->id, 4, '0', STR_PAD_LEFT),
                'date' => $match->match_date?->toDateString() ?? $selectedDate,
                'time' => $match->match_date?->format('H:i') ?? 'TBD',
                'players' => ($match->player1?->name ?? 'TBD') . ' vs ' . ($match->player2?->name ?? 'TBD'),
                'tournament' => Str::headline($match->status ?: 'scheduled'),
                'court' => $match->location ?: 'Court TBD',
                'score' => $this->score($match),
                'status' => $this->displayStatus($match->status),
            ])
            ->all();

        if ($matches === [] && GameMatch::count() === 0) {
            $month = AdminMockData::scheduleMonth();
            $selectedDate = $request->query('date', '2026-06-04');
            $matches = array_values(array_filter(AdminMockData::matches(), fn ($match) => $match['date'] === $selectedDate));
        }

        $calendarStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $calendarDays = [];

        for ($i = 0; $i < 35; $i++) {
            $calendarDays[] = $calendarStart->copy()->addDays($i);
        }

        return view('admin.schedule', compact('month', 'calendarDays', 'selectedDate', 'matches'));
    }

    private function score(GameMatch $match): string
    {
        if ($match->player1_score === null && $match->player2_score === null) {
            return '—';
        }

        return ($match->player1_score ?? '—') . ' - ' . ($match->player2_score ?? '—');
    }

    private function displayStatus(?string $status): string
    {
        return match ($status) {
            'in_progress' => 'Live',
            'completed' => 'Completed',
            'cancelled' => 'Paused',
            'open', 'scheduled' => 'Scheduled',
            default => Str::headline($status ?: 'Scheduled'),
        };
    }
}
