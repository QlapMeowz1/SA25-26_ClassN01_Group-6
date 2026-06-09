<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourtBookingsController extends Controller
{
    public function index()
    {
        $courts = ['Court 1', 'Court 2', 'Court 3', 'Court 4', 'Court 5', 'Court 6'];
        $slots = $this->buildSlots($courts);

        return view('admin.court-bookings', compact('courts', 'slots'));
    }

    public function create()
    {
        $players = User::orderBy('name')->get(['id', 'name']);
        $courts = ['Court 1', 'Court 2', 'Court 3', 'Court 4', 'Court 5', 'Court 6'];

        return view('admin.court-bookings-create', compact('players', 'courts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'court' => ['required', Rule::in(['Court 1', 'Court 2', 'Court 3', 'Court 4', 'Court 5', 'Court 6'])],
            'player1_id' => ['required', 'exists:users,id'],
            'player2_id' => ['nullable', 'different:player1_id', 'exists:users,id'],
            'status' => ['required', Rule::in(['open', 'scheduled', 'in_progress'])],
        ]);

        GameMatch::create([
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'] ?? null,
            'status' => $data['status'],
            'match_date' => "{$data['date']} {$data['time']}:00",
            'location' => $data['court'],
        ]);

        return redirect()->route('admin.court-bookings')->with('success', 'Đã tạo booking mới.');
    }

    private function buildSlots(array $courts): array
    {
        $hours = range(8, 20);
        $slots = [];

        foreach ($hours as $hour) {
            $row = ['time' => sprintf('%02d:00', $hour)];
            foreach ($courts as $index => $court) {
                $row['court_' . ($index + 1)] = 'available';
            }
            $slots[] = $row;
        }

        $matches = GameMatch::with(['player1', 'player2'])
            ->whereDate('match_date', now()->toDateString())
            ->orderBy('match_date')
            ->get();

        if (config('app.demo_data') && $matches->isEmpty()) {
            return AdminMockData::courtBookings();
        }

        foreach ($matches as $match) {
            $time = $match->match_date?->format('H:00');
            $rowIndex = array_search($time, array_column($slots, 'time'), true);

            if ($rowIndex === false) {
                continue;
            }

            $courtKey = 'court_' . $this->courtNumber($match->location);
            $slots[$rowIndex][$courtKey] = [
                'booked',
                ($match->player1?->name ?? 'TBD') . ' vs ' . ($match->player2?->name ?? 'TBD'),
                Str::headline($match->status ?: 'scheduled'),
            ];
        }

        $slots[6]['court_2'] = ['maintenance', 'Maintenance', ''];
        $slots[8]['court_5'] = ['maintenance', 'Maintenance', ''];

        return $slots;
    }

    private function courtNumber(?string $location): int
    {
        if ($location && preg_match('/court\s*(\d+)/i', $location, $matches)) {
            return min(6, max(1, (int) $matches[1]));
        }

        return 1;
    }
}
