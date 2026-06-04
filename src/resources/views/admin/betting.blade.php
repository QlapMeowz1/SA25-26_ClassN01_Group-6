@extends('layout')

@section('title', 'Cá Cược - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Cá Cược</h1>
            <p class="page-subtitle">Quản lý kèo đấu, quỹ cược, hoa hồng và giao dịch thanh toán.</p>
        </div>
    </section>

    <section class="admin-panel">
        <form method="GET" action="{{ route('admin.betting') }}" class="admin-filter-bar">
            <select name="status">
                <option value="">Tất cả trạng thái</option>
                @foreach(['Đang mở', 'Tạm dừng', 'Đã khóa'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-small">Lọc</button>
            <a href="{{ route('admin.betting') }}" class="btn btn-secondary btn-small">Đặt lại</a>
        </form>
    </section>

    <section class="admin-betting-layout">
        <div class="admin-panel">
            <div class="admin-panel-heading"><h2>Danh sách kèo</h2></div>
            <div class="admin-row-list">
                @foreach($tickets as $ticket)
                    <a href="{{ route('admin.betting', array_filter(['status' => $status, 'match' => $ticket['match_id'] ?? null])) }}" class="admin-bet-card">
                        <div>
                            <strong>{{ $ticket['match'] }}</strong>
                            <span>{{ $ticket['id'] }} · Tổng quỹ {{ number_format($ticket['pool']) }} xu</span>
                        </div>
                        <div class="admin-odds">
                            <span>{{ $ticket['odds_a'] }}</span>
                            <span>{{ $ticket['odds_b'] }}</span>
                        </div>
                        <span class="admin-pill">{{ $ticket['status'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <aside class="admin-panel">
            <div class="admin-panel-heading"><h2>Chi tiết kèo {{ $selectedTicket['id'] }}</h2></div>
            <div class="admin-bet-detail">
                <strong>{{ $selectedTicket['match'] }}</strong>
                <p>Phân bổ tiền cược</p>
                <div class="admin-distribution">
                    <div style="width: {{ $selectedTicket['a_percent'] }}%"></div>
                    <span>{{ $selectedTicket['a_percent'] }}%</span>
                </div>
                <dl>
                    <div><dt>Tổng quỹ</dt><dd>{{ number_format($selectedTicket['pool']) }} xu</dd></div>
                    <div><dt>Hoa hồng hệ thống</dt><dd>{{ number_format($selectedTicket['commission']) }} xu</dd></div>
                    <div><dt>Chi trả tiềm năng</dt><dd>{{ number_format($selectedTicket['potential_payout']) }} xu</dd></div>
                </dl>
                @if(isset($selectedTicket['match_id']))
                    <form method="POST" action="{{ route('admin.betting.odds.update', $selectedTicket['match_id']) }}" class="admin-record-form admin-odds-form">
                        @csrf
                        <div class="admin-form-grid">
                            <label>
                                <span>{{ $selectedTicket['player1_name'] ?? 'Player 1' }} odds</span>
                                <input type="number" name="player1_odds" min="1.01" max="50" step="0.01" value="{{ $selectedTicket['odds_a'] }}" required>
                            </label>
                            <label>
                                <span>{{ $selectedTicket['player2_name'] ?? 'Player 2' }} odds</span>
                                <input type="number" name="player2_odds" min="1.01" max="50" step="0.01" value="{{ $selectedTicket['odds_b'] }}" required>
                            </label>
                        </div>
                        <small>{{ ($selectedTicket['is_manual'] ?? false) ? 'Tỉ lệ thủ công đang được áp dụng.' : 'Đang dùng tỉ lệ tự động từ hệ thống.' }}</small>
                        <div class="admin-form-actions">
                            <button type="submit" class="btn btn-primary btn-small">Cập nhật tỉ lệ</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.betting.odds.delete', $selectedTicket['match_id']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-small">Xóa tỉ lệ thủ công</button>
                    </form>
                @endif
                <div class="admin-form-actions">
                    <form method="POST" action="{{ route('admin.betting.approve', $selectedTicket['id']) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-small">Duyệt kèo</button>
                    </form>
                    <form method="POST" action="{{ route('admin.betting.cancel', $selectedTicket['id']) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-small">Hủy kèo</button>
                    </form>
                </div>
            </div>
        </aside>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading"><h2>Giao dịch cược</h2></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Mã GD</th><th>Người chơi</th><th>Vé</th><th>Tiền cược</th><th>Lựa chọn</th><th>Chi trả dự kiến</th><th>Trạng thái</th></tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction['id'] }}</td>
                            <td><strong>{{ $transaction['user'] }}</strong></td>
                            <td>{{ $transaction['ticket'] }}</td>
                            <td>{{ number_format($transaction['stake']) }}</td>
                            <td>{{ $transaction['pick'] }}</td>
                            <td>{{ number_format($transaction['potential']) }}</td>
                            <td><span class="admin-pill">{{ $transaction['status'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
