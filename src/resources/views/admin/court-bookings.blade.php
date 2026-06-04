@extends('layout')

@section('title', 'Court Bookings - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Court Bookings</h1>
            <p class="page-subtitle">Today, Jun 4 · 14% utilization · 11 slots booked</p>
        </div>
        <a href="{{ route('admin.court-bookings.create') }}" class="btn btn-primary">＋ New Booking</a>
    </section>

    <div class="admin-booking-legend">
        <span><i class="slot--available"></i> Available</span>
        <span><i class="slot--booked"></i> Booked</span>
        <span><i class="slot--maintenance"></i> Maintenance</span>
    </div>

    <section class="admin-panel">
        <div class="admin-booking-grid">
            <div class="admin-booking-head">Time</div>
            @foreach($courts as $court)
                <div class="admin-booking-head">{{ $court }}</div>
            @endforeach
            @foreach($slots as $row)
                <div class="admin-booking-time">{{ $row['time'] }}</div>
                @foreach(['court_1', 'court_2', 'court_3', 'court_4', 'court_5', 'court_6'] as $courtKey)
                    @php
                        $slot = $row[$courtKey];
                        $type = is_array($slot) ? $slot[0] : $slot;
                    @endphp
                    <div class="admin-booking-cell slot--{{ $type }}">
                        @if(is_array($slot))
                            <strong>{{ $slot[1] }}</strong>
                            @if($slot[2])
                                <span>{{ $slot[2] }}</span>
                            @endif
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    </section>
</div>
@endsection
