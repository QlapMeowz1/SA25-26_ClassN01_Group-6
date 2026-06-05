@extends('layout')

@section('title', 'Betting - SMASH Admin')

@php
    $money = fn ($value) => '₫' . number_format((float) $value, 0, ',', '.');
    $shortMoney = function ($value) {
        $value = (float) $value;
        if ($value >= 1000000) {
            return '₫' . rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'M';
        }
        if ($value >= 1000) {
            return '₫' . rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'K';
        }
        return '₫' . number_format($value);
    };
    $stateClass = fn ($state) => 'admin-betting-status--' . ($state ?: 'open');
    $revenueBars = [
        ['day' => 'T2', 'height' => 46, 'value' => '₫4.6M'],
        ['day' => 'T3', 'height' => 70, 'value' => '₫7.0M'],
        ['day' => 'T4', 'height' => 50, 'value' => '₫5.0M'],
        ['day' => 'T5', 'height' => 92, 'value' => '₫9.2M'],
        ['day' => 'T6', 'height' => 114, 'value' => '₫11.4M'],
        ['day' => 'T7', 'height' => 190, 'value' => '₫19.0M'],
        ['day' => 'CN', 'height' => 145, 'value' => '₫14.5M'],
    ];
    $oddsPoints = [
        ['time' => '06:00', 'x' => 0, 'blueY' => 36, 'blue' => 2.60, 'limeY' => 66, 'lime' => 1.50],
        ['time' => '07:00', 'x' => 20, 'blueY' => 31, 'blue' => 2.72, 'limeY' => 67, 'lime' => 1.48],
        ['time' => '07:30', 'x' => 38, 'blueY' => 28, 'blue' => 2.84, 'limeY' => 68, 'lime' => 1.46],
        ['time' => '08:00', 'x' => 57, 'blueY' => 26, 'blue' => 2.90, 'limeY' => 69, 'lime' => 1.44],
        ['time' => '08:30', 'x' => 78, 'blueY' => 26, 'blue' => 2.88, 'limeY' => 69, 'lime' => 1.45],
        ['time' => '09:00', 'x' => 100, 'blueY' => 22, 'blue' => 3.00, 'limeY' => 70, 'lime' => 1.43],
    ];
@endphp

@section('content')
<div class="page-shell admin-console-page admin-betting-pro">
    @include('admin.partials.nav')

    <section class="admin-page-header admin-betting-header">
        <div>
            <h1>Betting</h1>
            <p class="page-subtitle">Manage odds, transactions, and payouts</p>
        </div>
        <a href="{{ route('matches.create') }}" class="admin-create-market-btn">
            <span>+</span>
            Tạo kèo mới
        </a>
    </section>

    <section class="admin-betting-kpis">
        @foreach($stats as $stat)
            <article class="admin-betting-kpi admin-betting-kpi--{{ $stat['tone'] }}">
                <div>
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>
                        @if($stat['change'])
                            <b>{{ $stat['change'] }}</b>
                        @endif
                        {{ $stat['note'] }}
                    </small>
                </div>
                <i>{{ $stat['icon'] }}</i>
            </article>
        @endforeach
    </section>

    <section class="admin-betting-chart-grid">
        <article class="admin-panel admin-betting-chart-card">
            <div class="admin-panel-heading">
                <h2>Doanh thu tuần này</h2>
            </div>
            <div
                class="admin-betting-bars"
                aria-label="Doanh thu tuần này"
                data-admin-cursor-chart="{{ collect($revenueBars)->map(fn ($bar) => $bar['day'] . ': ' . $bar['value'])->implode('|') }}"
            >
                @foreach($revenueBars as $bar)
                    <span
                        tabindex="0"
                        style="--bar-height: {{ $bar['height'] }}px"
                        data-tooltip="{{ $bar['day'] }}: {{ $bar['value'] }}"
                        title="{{ $bar['day'] }}: {{ $bar['value'] }}"
                    ><i></i></span>
                @endforeach
            </div>
            <div class="admin-betting-axis"><span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span></div>
        </article>

        <article class="admin-panel admin-betting-chart-card">
            <div class="admin-panel-heading">
                <h2>Biến động tỷ lệ</h2>
                <span>{{ $selectedTicket['match'] ?? 'Kèo đang chọn' }}</span>
            </div>
            <div
                class="admin-betting-line-chart"
                aria-label="Biến động tỷ lệ cược"
                data-admin-cursor-chart="{{ collect($oddsPoints)->map(fn ($point) => $point['time'] . ' · ' . ($selectedTicket['player1_name'] ?? 'Player 1') . ' ' . number_format($point['lime'], 2) . ' / ' . ($selectedTicket['player2_name'] ?? 'Player 2') . ' ' . number_format($point['blue'], 2))->implode('|') }}"
            >
                <svg viewBox="0 0 700 190" preserveAspectRatio="none">
                    <path class="line-blue" d="M0 70 C120 62 190 58 270 54 C380 47 470 52 560 49 C620 48 660 42 700 39"/>
                    <path class="line-lime" d="M0 125 C110 128 190 128 270 130 C380 134 460 131 560 131 C620 132 660 136 700 137"/>
                </svg>
                @foreach($oddsPoints as $point)
                    <span
                        class="admin-chart-point admin-chart-point--blue"
                        style="--x: {{ $point['x'] }}%; --y: {{ $point['blueY'] }}%;"
                        aria-hidden="true"
                    ></span>
                    <span
                        class="admin-chart-point admin-chart-point--lime"
                        style="--x: {{ $point['x'] }}%; --y: {{ $point['limeY'] }}%;"
                        aria-hidden="true"
                    ></span>
                @endforeach
                <div class="admin-chart-hover-zones">
                    @foreach($oddsPoints as $point)
                        <span
                            tabindex="0"
                            data-tooltip="{{ $point['time'] }} · {{ $selectedTicket['player1_name'] ?? 'Player 1' }} {{ number_format($point['lime'], 2) }} / {{ $selectedTicket['player2_name'] ?? 'Player 2' }} {{ number_format($point['blue'], 2) }}"
                            title="{{ $point['time'] }} · {{ $selectedTicket['player1_name'] ?? 'Player 1' }} {{ number_format($point['lime'], 2) }} / {{ $selectedTicket['player2_name'] ?? 'Player 2' }} {{ number_format($point['blue'], 2) }}"
                        ></span>
                    @endforeach
                </div>
                <div class="admin-betting-line-scale">
                    <span>3.2</span><span>2.7</span><span>2.2</span><span>1.7</span><span>1.2</span>
                </div>
            </div>
            <div class="admin-betting-axis"><span>06:00</span><span>07:00</span><span>07:30</span><span>08:00</span><span>08:30</span><span>09:00</span></div>
        </article>
    </section>

    <section class="admin-betting-workspace">
        <main>
            <form method="GET" action="{{ route('admin.betting') }}" class="admin-betting-toolbar">
                <label class="admin-betting-search">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tìm trận đấu...">
                </label>
                <div class="admin-betting-tabs">
                    @foreach([
                        'all' => 'Tất cả',
                        'open' => 'Open',
                        'live' => 'Live',
                        'suspended' => 'Suspended',
                        'settled' => 'Settled',
                    ] as $value => $label)
                        <button type="submit" name="status" value="{{ $value }}" class="{{ $filters['status'] === $value ? 'is-active' : '' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </form>

            <section class="admin-betting-market-table">
                <div class="admin-betting-market-head">
                    <span>Trận đấu</span>
                    <span>Quỹ cược</span>
                    <span>Tỷ lệ</span>
                    <span>Trạng thái</span>
                    <span></span>
                </div>
                @forelse($tickets as $ticket)
                    <a href="{{ route('admin.betting', array_filter(['q' => $filters['q'], 'status' => $filters['status'], 'match' => $ticket['match_id'] ?? null])) }}"
                       class="admin-betting-market-row {{ ($selectedTicket['match_id'] ?? null) === ($ticket['match_id'] ?? null) ? 'is-selected' : '' }}">
                        <div>
                            <strong>{{ $ticket['match'] }}</strong>
                            <small>{{ $ticket['event'] ?? 'Open Match' }} · {{ $ticket['time'] ?? '09:00' }}</small>
                        </div>
                        <div>
                            <strong class="admin-betting-money">{{ $shortMoney($ticket['pool'] ?? 0) }}</strong>
                            <small>{{ number_format($ticket['bettor_count'] ?? 0) }} người đặt</small>
                        </div>
                        <div class="admin-betting-odds-inline">
                            <b>{{ $ticket['odds_a'] }}</b>
                            <span>·</span>
                            <b>{{ $ticket['odds_b'] }}</b>
                        </div>
                        <div>
                            <span class="admin-betting-status {{ $stateClass($ticket['state'] ?? 'open') }}">{{ $ticket['status'] }}</span>
                        </div>
                        <span class="admin-betting-more">•••</span>
                    </a>
                @empty
                    <div class="admin-betting-empty">Không tìm thấy kèo phù hợp.</div>
                @endforelse
            </section>
        </main>

        <aside class="admin-betting-detail-card">
            <header>
                <span>Chi tiết kèo</span>
                <strong>{{ $selectedTicket['match'] }}</strong>
                <small>{{ $selectedTicket['event'] ?? 'Open Match' }} · {{ $selectedTicket['date'] ?? 'Jun 4, 2026' }}</small>
            </header>

            <form method="POST" action="{{ isset($selectedTicket['match_id']) ? route('admin.betting.odds.update', $selectedTicket['match_id']) : '#' }}">
                @csrf
                <section>
                    <p>Tỷ lệ cược</p>
                    <div class="admin-betting-odds-grid">
                        <label>
                            <span>{{ $selectedTicket['player1_name'] ?? 'Player 1' }}</span>
                            <input type="number" name="player1_odds" min="1.01" max="50" step="0.01" value="{{ $selectedTicket['odds_a'] }}" required>
                            <small>{{ $shortMoney($selectedTicket['player1_pool'] ?? 0) }} · {{ number_format($selectedTicket['a_percent'] ?? 50) }}%</small>
                        </label>
                        <label>
                            <span>{{ $selectedTicket['player2_name'] ?? 'Player 2' }}</span>
                            <input type="number" name="player2_odds" min="1.01" max="50" step="0.01" value="{{ $selectedTicket['odds_b'] }}" required>
                            <small>{{ $shortMoney($selectedTicket['player2_pool'] ?? 0) }} · {{ number_format($selectedTicket['b_percent'] ?? 50) }}%</small>
                        </label>
                    </div>
                </section>

                <section>
                    <div class="admin-betting-split-title">
                        <p>Phân bổ cược</p>
                        <span>{{ $selectedTicket['a_percent'] ?? 50 }}% / {{ $selectedTicket['b_percent'] ?? 50 }}%</span>
                    </div>
                    <div class="admin-betting-split">
                        <div style="width: {{ $selectedTicket['a_percent'] ?? 50 }}%"></div>
                    </div>
                    <div class="admin-betting-split-labels">
                        <span>{{ $selectedTicket['player1_name'] ?? 'Player 1' }}</span>
                        <span>{{ $selectedTicket['player2_name'] ?? 'Player 2' }}</span>
                    </div>
                </section>

                <dl>
                    <div><dt>Tổng quỹ</dt><dd>{{ $money($selectedTicket['pool'] ?? 0) }}</dd></div>
                    <div><dt>Hoa hồng (5%)</dt><dd>{{ $money($selectedTicket['commission'] ?? 0) }}</dd></div>
                    <div><dt>Quỹ thanh toán</dt><dd>{{ $money($selectedTicket['potential_payout'] ?? 0) }}</dd></div>
                </dl>

                @if(isset($selectedTicket['match_id']))
                    <button type="submit" class="admin-betting-primary-action">Cập nhật tỷ lệ</button>
                @endif
            </form>

            @if(isset($selectedTicket['match_id']))
                <form method="POST" action="{{ route('admin.betting.cancel', $selectedTicket['match_id']) }}" onsubmit="return confirm('Tạm dừng nhận cược cho kèo này?');">
                    @csrf
                    <button type="submit" class="admin-betting-danger-action">Tạm dừng nhận cược</button>
                </form>
            @endif
        </aside>
    </section>

    <section class="admin-betting-transactions">
        <div class="admin-panel-heading">
            <h2>Giao dịch gần đây</h2>
            <a href="{{ route('admin.betting') }}">Xem tất cả →</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã GD</th>
                        <th>Người dùng</th>
                        <th>Trận đấu</th>
                        <th>Lựa chọn</th>
                        <th>Số tiền</th>
                        <th>Tỷ lệ</th>
                        <th>Tiềm năng</th>
                        <th>TT</th>
                        <th>Giờ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction['id'] }}</td>
                            <td><strong>{{ $transaction['user'] }}</strong></td>
                            <td>{{ $transaction['match'] ?? $transaction['ticket'] }}</td>
                            <td><strong class="admin-betting-pick">{{ $transaction['pick'] }}</strong></td>
                            <td>{{ $money($transaction['stake']) }}</td>
                            <td><strong class="admin-betting-blue">{{ $transaction['odds'] ?? '1.00' }}</strong></td>
                            <td><strong class="admin-betting-purple">{{ $money($transaction['potential']) }}</strong></td>
                            <td><span class="admin-betting-txn-status admin-betting-txn-status--{{ strtolower($transaction['status']) }}">{{ $transaction['status'] }}</span></td>
                            <td>{{ $transaction['time'] ?? '08:42' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
