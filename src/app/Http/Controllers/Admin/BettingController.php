<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\BetService;
use App\Services\AuditService;

class BettingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeBettingManagement();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', 'all'),
        ];

        $allTickets = $this->tickets();
        $tickets = $allTickets;

        if ($filters['q'] !== '') {
            $tickets = array_values(array_filter($tickets, function ($ticket) use ($filters) {
                return Str::contains(Str::lower($ticket['match']), Str::lower($filters['q']))
                    || Str::contains(Str::lower($ticket['event'] ?? ''), Str::lower($filters['q']));
            }));
        }

        if ($filters['status'] !== 'all') {
            $tickets = array_values(array_filter($tickets, fn ($ticket) => ($ticket['state'] ?? 'open') === $filters['status']));
        }

        $selectedMatch = $request->query('match');
        $selectedTicket = collect($tickets)->firstWhere('match_id', $selectedMatch ? (int) $selectedMatch : null)
            ?? ($tickets[0] ?? $allTickets[0] ?? (config('app.demo_data') ? AdminMockData::betting()[0] : []));
        $transactions = $this->transactions();
        $stats = $this->stats($allTickets, $transactions);

        return view('admin.betting', compact('tickets', 'selectedTicket', 'transactions', 'filters', 'stats'));
    }

    public function approve(GameMatch $match, AuditService $audit)
    {
        $this->authorizeBettingManagement();

        if (!$match->player1_id || !$match->player2_id) {
            return back()->with('error', 'Kèo cần đủ 2 người chơi trước khi mở.');
        }

        if (in_array($match->status, ['completed', 'cancelled'], true)) {
            return back()->with('error', 'Không thể mở kèo cho trận đã hoàn tất hoặc đã hủy.');
        }

        $before = $match->only(['betting_status', 'odds_updated_by', 'odds_updated_at']);
        $match->update([
            'betting_status' => 'open',
            'odds_updated_by' => auth()->id(),
            'odds_updated_at' => now(),
        ]);
        $audit->record('betting.market_approved', $match, $before, $match->fresh()->only(array_keys($before)));

        return back()->with('success', 'Đã duyệt và mở kèo cá cược.');
    }

    public function cancel(GameMatch $match, BetService $betService, AuditService $audit)
    {
        $this->authorizeBettingManagement();

        $before = $match->only(['betting_status', 'odds_updated_by', 'odds_updated_at']);
        DB::transaction(function () use ($match, $betService) {
            $match->update([
                'betting_status' => 'cancelled',
                'odds_updated_by' => auth()->id(),
                'odds_updated_at' => now(),
            ]);

            $betService->refundMatch($match, auth()->user());
        });
        $audit->record('betting.market_cancelled', $match, $before, $match->fresh()->only(array_keys($before)));

        $this->notifyBettors($match, 'betting_cancelled', 'Betting Refunded', 'Betting has been suspended for ' . $this->matchLabel($match) . '. Pending stakes were returned to wallets.');
        app(BetService::class)->broadcastPoolUpdate($match, true);

        return back()->with('success', 'Đã tạm dừng kèo và hoàn điểm cho các vé đang chờ.');
    }

    public function updateOdds(GameMatch $match, Request $request, AuditService $audit)
    {
        $this->authorizeBettingManagement();

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

        $before = $match->only(['player1_odds', 'player2_odds', 'odds_updated_by', 'odds_updated_at']);
        $match->update([
            'player1_odds' => round((float) $validated['player1_odds'], 2),
            'player2_odds' => round((float) $validated['player2_odds'], 2),
            'odds_updated_by' => auth()->id(),
            'odds_updated_at' => now(),
        ]);
        $audit->record('betting.odds_updated', $match, $before, $match->fresh()->only(array_keys($before)));

        $this->notifyBettors(
            $match,
            'odds_changed',
            'Odds Changed',
            'Odds changed for ' . $this->matchLabel($match) . ': ' . number_format((float) $validated['player1_odds'], 2) . ' / ' . number_format((float) $validated['player2_odds'], 2) . '.'
        );

        return back()->with('success', 'Đã cập nhật tỉ lệ cá cược.');
    }

    public function deleteOdds(GameMatch $match, AuditService $audit)
    {
        $this->authorizeBettingManagement();

        $before = $match->only(['player1_odds', 'player2_odds', 'odds_updated_by', 'odds_updated_at']);
        $match->update([
            'player1_odds' => null,
            'player2_odds' => null,
            'odds_updated_by' => auth()->id(),
            'odds_updated_at' => now(),
        ]);
        $audit->record('betting.odds_deleted', $match, $before, $match->fresh()->only(array_keys($before)));

        return back()->with('success', 'Đã xóa tỉ lệ thủ công. Hệ thống sẽ tự tính lại.');
    }

    private function tickets(): array
    {
        $matches = GameMatch::with(['player1', 'player2', 'bets'])
            ->whereNotNull('player2_id')
            ->whereIn('status', ['open', 'scheduled', 'in_progress', 'completed', 'cancelled'])
            ->latest('match_date')
            ->get();

        if (config('app.demo_data') && $matches->isEmpty()) {
            return AdminMockData::betting();
        }

        $tickets = $matches->map(function (GameMatch $match) {
            $odds = app(BetService::class)->getMatchOdds($match);
            $pool = (float) $match->bets->sum('amount');
            $playerOnePool = (float) $match->bets->where('bet_on_user_id', $match->player1_id)->sum('amount');
            $playerOnePercent = $pool > 0 ? (int) round(($playerOnePool / $pool) * 100) : 50;
            $state = $this->marketState($match);

            return [
                'match_id' => $match->id,
                'id' => 'BT-' . str_pad((string) $match->id, 3, '0', STR_PAD_LEFT),
                'match' => ($match->player1?->name ?? 'TBD') . ' vs ' . ($match->player2?->name ?? 'TBD'),
                'player1_name' => $match->player1?->name ?? 'Player 1',
                'player2_name' => $match->player2?->name ?? 'Player 2',
                'event' => $match->challenge ? 'Challenge Match' : 'Open Match',
                'time' => optional($match->match_date)->format('H:i'),
                'date' => optional($match->match_date)->format('M j, Y'),
                'odds_a' => number_format((float) $odds['player1_odds'], 2),
                'odds_b' => number_format((float) $odds['player2_odds'], 2),
                'pool' => $pool,
                'bettor_count' => $match->bets->pluck('user_id')->unique()->count(),
                'status' => $this->bettingStatus($state),
                'state' => $state,
                'market_status' => $match->betting_status ?? 'open',
                'a_percent' => $playerOnePercent,
                'b_percent' => 100 - $playerOnePercent,
                'player1_pool' => $playerOnePool,
                'player2_pool' => max(0, $pool - $playerOnePool),
                'commission' => round($pool * 0.05),
                'potential_payout' => round($pool * 0.95),
                'is_manual' => (bool) ($odds['is_manual'] ?? false),
            ];
        })->all();

        if (config('app.demo_data') && (count($tickets) < 8 || array_sum(array_column($tickets, 'pool')) < 100000)) {
            $existingNames = collect($tickets)->pluck('match')->map(fn ($name) => Str::lower($name))->all();
            $supplemental = array_values(array_filter(AdminMockData::betting(), function ($ticket) use ($existingNames) {
                return !in_array(Str::lower($ticket['match']), $existingNames, true);
            }));

            $tickets = array_slice(array_merge($tickets, $supplemental), 0, 10);
        }

        return $tickets;
    }

    private function notifyBettors(GameMatch $match, string $type, string $title, string $message): void
    {
        $userIds = Bet::where('match_id', $match->id)
            ->whereIn('status', ['pending', 'live', 'refunded'])
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'related_user_id' => auth()->id(),
                'target_url' => route('matches.show', $match->id),
            ]);
        }
    }

    private function matchLabel(GameMatch $match): string
    {
        $match->loadMissing(['player1', 'player2']);

        return ($match->player1?->name ?? 'Player 1') . ' vs ' . ($match->player2?->name ?? 'Player 2');
    }

    private function transactions(): array
    {
        $transactions = Bet::with(['user', 'gameMatch', 'betOnUser'])
            ->latest()
            ->limit(10)
            ->get();

        if (config('app.demo_data') && $transactions->isEmpty()) {
            return AdminMockData::transactions();
        }

        $rows = $transactions->map(fn (Bet $bet) => [
            'id' => 'TXN-' . str_pad((string) $bet->id, 4, '0', STR_PAD_LEFT),
            'user' => $bet->user?->name ?? 'Unknown',
            'ticket' => 'BT-' . str_pad((string) $bet->match_id, 3, '0', STR_PAD_LEFT),
            'match' => trim(($bet->gameMatch?->player1?->name ?? 'TBD') . ' vs ' . ($bet->gameMatch?->player2?->name ?? 'TBD')),
            'stake' => (float) $bet->amount,
            'pick' => $bet->betOnUser?->name ?? 'TBD',
            'odds' => (float) ($bet->odds ?: 1),
            'potential' => (float) ($bet->payout ?: $bet->calculatePayout()),
            'status' => Str::headline($bet->status ?: 'pending'),
            'time' => optional($bet->created_at)->isToday() ? $bet->created_at->format('H:i') : optional($bet->created_at)->diffForHumans(),
        ])->all();

        if (config('app.demo_data') && count($rows) < 10) {
            $existingIds = collect($rows)->pluck('id')->all();
            $supplemental = array_values(array_filter(AdminMockData::transactions(), function ($transaction) use ($existingIds) {
                return !in_array($transaction['id'], $existingIds, true);
            }));

            $rows = array_slice(array_merge($rows, $supplemental), 0, 12);
        }

        return $rows;
    }

    private function authorizeBettingManagement(): void
    {
        abort_unless(auth()->user()?->canManageBetting(), 403);
    }

    private function stats(array $tickets, array $transactions): array
    {
        $totalPool = array_sum(array_column($tickets, 'pool'));
        $commission = array_sum(array_column($tickets, 'commission'));
        $bettors = collect($transactions)->pluck('user')->filter()->unique()->count();

        return [
            ['label' => 'Total Pool', 'value' => $this->moneyShort($totalPool), 'icon' => '$', 'change' => '+18%', 'note' => 'this week', 'tone' => 'lime'],
            ['label' => 'Open Markets', 'value' => (string) collect($tickets)->whereIn('state', ['open', 'live'])->count(), 'icon' => '↗', 'change' => '', 'note' => 'accepting stakes', 'tone' => 'blue'],
            ['label' => 'Bettors', 'value' => number_format(max($bettors, 0)), 'icon' => '♙', 'change' => '+124', 'note' => 'today', 'tone' => 'purple'],
            ['label' => 'Commission Revenue', 'value' => $this->moneyShort($commission), 'icon' => '⌁', 'change' => '+22%', 'note' => '5% system tax', 'tone' => 'green'],
        ];
    }

    private function marketState(GameMatch $match): string
    {
        if (in_array($match->betting_status, ['cancelled', 'suspended'], true)) {
            return 'suspended';
        }

        if ($match->status === 'completed') {
            return 'settled';
        }

        if ($match->status === 'in_progress') {
            return 'live';
        }

        return 'open';
    }

    private function bettingStatus(string $state): string
    {
        return match ($state) {
            'live' => 'Live',
            'suspended' => 'Suspended',
            'settled' => 'Settled',
            default => 'Open',
        };
    }

    private function moneyShort(float|int $amount): string
    {
        if ($amount >= 1000000) {
            return '₫' . rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.') . 'M';
        }

        if ($amount >= 1000) {
            return '₫' . rtrim(rtrim(number_format($amount / 1000, 1), '0'), '.') . 'K';
        }

        return '₫' . number_format($amount);
    }
}
