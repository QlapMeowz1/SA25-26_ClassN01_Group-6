<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\GameMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\BetService;

class BettingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $tickets = $this->tickets();
        if ($status) {
            $tickets = array_values(array_filter($tickets, fn ($ticket) => $ticket['status'] === $status));
        }

        $selectedMatch = $request->query('match');
        $selectedTicket = collect($tickets)->firstWhere('match_id', $selectedMatch ? (int) $selectedMatch : null)
            ?? ($tickets[0] ?? AdminMockData::betting()[0]);
        $transactions = $this->transactions();

        return view('admin.betting', compact('tickets', 'selectedTicket', 'transactions', 'status'));
    }

    public function approve(string $ticket)
    {
        return back()->with('success', "Đã duyệt vé {$ticket}.");
    }

    public function cancel(string $ticket)
    {
        return back()->with('success', "Đã hủy vé {$ticket}.");
    }

    public function updateOdds(GameMatch $match, Request $request)
    {
        $validated = $request->validate([
            'player1_odds' => ['required', 'numeric', 'min:1.01', 'max:50'],
            'player2_odds' => ['required', 'numeric', 'min:1.01', 'max:50'],
        ]);

        if (!$match->player1_id || !$match->player2_id) {
            return back()->with('error', 'Kèo cần đủ 2 người chơi trước khi chỉnh tỉ lệ.');
        }

        if ($match->status === 'completed') {
            return back()->with('error', 'Không thể chỉnh tỉ lệ sau khi trận đã hoàn tất.');
        }

        $match->update([
            'player1_odds' => round((float) $validated['player1_odds'], 2),
            'player2_odds' => round((float) $validated['player2_odds'], 2),
            'odds_updated_by' => auth()->id(),
            'odds_updated_at' => now(),
        ]);

        return back()->with('success', 'Đã cập nhật tỉ lệ cá cược.');
    }

    public function deleteOdds(GameMatch $match)
    {
        $match->update([
            'player1_odds' => null,
            'player2_odds' => null,
            'odds_updated_by' => auth()->id(),
            'odds_updated_at' => now(),
        ]);

        return back()->with('success', 'Đã xóa tỉ lệ thủ công. Hệ thống sẽ tự tính lại.');
    }

    private function tickets(): array
    {
        $matches = GameMatch::with(['player1', 'player2', 'bets'])
            ->whereNotNull('player2_id')
            ->whereIn('status', ['open', 'scheduled', 'in_progress', 'completed', 'cancelled'])
            ->latest('match_date')
            ->get();

        if ($matches->isEmpty()) {
            return AdminMockData::betting();
        }

        return $matches->map(function (GameMatch $match) {
            $odds = app(BetService::class)->getMatchOdds($match);
            $pool = (float) $match->bets->sum('amount');
            $playerOnePool = (float) $match->bets->where('bet_on_user_id', $match->player1_id)->sum('amount');
            $playerOnePercent = $pool > 0 ? (int) round(($playerOnePool / $pool) * 100) : 50;

            return [
                'match_id' => $match->id,
                'id' => 'BT-' . str_pad((string) $match->id, 3, '0', STR_PAD_LEFT),
                'match' => ($match->player1?->name ?? 'TBD') . ' vs ' . ($match->player2?->name ?? 'TBD'),
                'player1_name' => $match->player1?->name ?? 'Player 1',
                'player2_name' => $match->player2?->name ?? 'Player 2',
                'odds_a' => number_format((float) $odds['player1_odds'], 2),
                'odds_b' => number_format((float) $odds['player2_odds'], 2),
                'pool' => $pool,
                'status' => $this->bettingStatus($match->status),
                'a_percent' => $playerOnePercent,
                'commission' => round($pool * 0.05),
                'potential_payout' => round($pool * 0.95),
                'is_manual' => (bool) ($odds['is_manual'] ?? false),
            ];
        })->all();
    }

    private function transactions(): array
    {
        $transactions = Bet::with(['user', 'gameMatch', 'betOnUser'])
            ->latest()
            ->limit(10)
            ->get();

        if ($transactions->isEmpty()) {
            return AdminMockData::transactions();
        }

        return $transactions->map(fn (Bet $bet) => [
            'id' => 'TX-' . str_pad((string) $bet->id, 4, '0', STR_PAD_LEFT),
            'user' => $bet->user?->name ?? 'Unknown',
            'ticket' => 'BT-' . str_pad((string) $bet->match_id, 3, '0', STR_PAD_LEFT),
            'stake' => (float) $bet->amount,
            'pick' => $bet->betOnUser?->name ?? 'TBD',
            'potential' => (float) ($bet->payout ?: $bet->calculatePayout()),
            'status' => Str::headline($bet->status ?: 'pending'),
        ])->all();
    }

    private function bettingStatus(?string $status): string
    {
        return match ($status) {
            'open', 'scheduled', 'in_progress' => 'Đang mở',
            'cancelled' => 'Tạm dừng',
            'completed' => 'Đã khóa',
            default => 'Đang mở',
        };
    }
}
