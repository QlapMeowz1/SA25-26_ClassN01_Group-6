<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;

class AdminMockData
{
    public static function players(): array
    {
        return [
            ['id' => 1, 'initials' => 'PS', 'name' => 'Priya Sharma', 'category' => "Women's Singles", 'club' => 'Bangalore Shuttlers', 'rank' => 1, 'wins' => 48, 'losses' => 9, 'rating' => 2840, 'status' => 'Active'],
            ['id' => 2, 'initials' => 'DK', 'name' => 'Dev Khanna', 'category' => "Men's Singles", 'club' => 'Delhi Smash', 'rank' => 2, 'wins' => 52, 'losses' => 11, 'rating' => 2790, 'status' => 'Active'],
            ['id' => 3, 'initials' => 'AR', 'name' => 'Ananya Roy', 'category' => "Women's Singles", 'club' => 'Kolkata Flyers', 'rank' => 3, 'wins' => 41, 'losses' => 14, 'rating' => 2710, 'status' => 'Active'],
            ['id' => 4, 'initials' => 'RM', 'name' => 'Rohan Mehta', 'category' => "Men's Singles", 'club' => 'Mumbai Hawks', 'rank' => 4, 'wins' => 39, 'losses' => 16, 'rating' => 2680, 'status' => 'Suspended'],
            ['id' => 5, 'initials' => 'SP', 'name' => 'Sita Patil', 'category' => "Women's Singles", 'club' => 'Pune Aces', 'rank' => 5, 'wins' => 37, 'losses' => 18, 'rating' => 2640, 'status' => 'Active'],
            ['id' => 6, 'initials' => 'VN', 'name' => 'Vikram Nair', 'category' => "Men's Singles", 'club' => 'Chennai Strikers', 'rank' => 6, 'wins' => 44, 'losses' => 15, 'rating' => 2610, 'status' => 'Active'],
            ['id' => 7, 'initials' => 'LH', 'name' => 'Layla Hassan', 'category' => "Women\'s Singles", 'club' => 'Hyderabad Eagles', 'rank' => 7, 'wins' => 35, 'losses' => 20, 'rating' => 2590, 'status' => 'Active'],
            ['id' => 8, 'initials' => 'AT', 'name' => 'Arun Tiwari', 'category' => "Men's Singles", 'club' => 'Jaipur Jets', 'rank' => 8, 'wins' => 30, 'losses' => 22, 'rating' => 2540, 'status' => 'Injured'],
            ['id' => 9, 'initials' => 'DC', 'name' => 'Divya Chopra', 'category' => "Women's Singles", 'club' => 'Lucknow Blazers', 'rank' => 9, 'wins' => 28, 'losses' => 23, 'rating' => 2510, 'status' => 'Active'],
            ['id' => 10, 'initials' => 'MJ', 'name' => 'Meera Joshi', 'category' => "Women\'s Singles", 'club' => 'Bangalore Shuttlers', 'rank' => 10, 'wins' => 26, 'losses' => 24, 'rating' => 2490, 'status' => 'Active'],
        ];
    }

    public static function matches(): array
    {
        return [
            ['id' => 'M-1042', 'date' => '2026-06-01', 'time' => '09:00', 'players' => 'Priya S. vs Ananya R.', 'tournament' => 'State Open 2026', 'court' => 'Court 1', 'score' => '21-18, 19-21, 21-14', 'status' => 'Completed'],
            ['id' => 'M-1041', 'date' => '2026-06-02', 'time' => '10:30', 'players' => 'Dev K. vs Rohan M.', 'tournament' => 'Club League Q2', 'court' => 'Court 2', 'score' => '21-15, 21-9', 'status' => 'Completed'],
            ['id' => 'M-1040', 'date' => '2026-06-04', 'time' => '12:00', 'players' => 'Sita P. vs Meera J.', 'tournament' => 'State Open 2026', 'court' => 'Court 3', 'score' => '—', 'status' => 'Live'],
            ['id' => 'M-1039', 'date' => '2026-06-04', 'time' => '14:00', 'players' => 'Vikram N. vs Arun T.', 'tournament' => 'Juniors Cup', 'court' => 'Court 1', 'score' => '18-21, 21-17, —', 'status' => 'Paused'],
            ['id' => 'M-1038', 'date' => '2026-06-04', 'time' => '15:00', 'players' => 'Layla H. vs Divya C.', 'tournament' => 'Club League Q2', 'court' => 'Court 4', 'score' => '21-10, 21-8', 'status' => 'Scheduled'],
            ['id' => 'M-1037', 'date' => '2026-06-05', 'time' => '09:00', 'players' => 'Dev Khanna vs Vikram Nair', 'tournament' => 'Club League Q2', 'court' => 'Court 2', 'score' => '—', 'status' => 'Scheduled'],
            ['id' => 'M-1036', 'date' => '2026-06-06', 'time' => '18:00', 'players' => 'Ananya Roy vs Meera Joshi', 'tournament' => 'State Open 2026', 'court' => 'Court 5', 'score' => '—', 'status' => 'Scheduled'],
        ];
    }

    public static function tournaments(): array
    {
        return [
            ['id' => 'state-open', 'name' => 'State Open Championship 2026', 'tag' => 'Open', 'venue' => 'Bangalore Indoor Stadium', 'dates' => 'May 28, 2026 - Jun 8, 2026', 'players' => 128, 'prize' => '₹5,00,000', 'status' => 'Ongoing', 'progress' => 65],
            ['id' => 'club-league', 'name' => 'Club League Q2 2026', 'tag' => 'Club', 'venue' => 'Delhi Sports Complex', 'dates' => 'Jun 1, 2026 - Jun 30, 2026', 'players' => 64, 'prize' => '₹80,000', 'status' => 'Ongoing', 'progress' => 40],
            ['id' => 'juniors-cup', 'name' => 'Juniors Cup 2026', 'tag' => 'U-18', 'venue' => 'Hyderabad Dome', 'dates' => 'Jun 10, 2026 - Jun 14, 2026', 'players' => 96, 'prize' => '₹1,20,000', 'status' => 'Upcoming', 'progress' => 8],
            ['id' => 'masters-50', 'name' => 'Masters 50+ Invitational', 'tag' => 'Veterans', 'venue' => 'Mumbai Arena', 'dates' => 'Jun 20, 2026 - Jun 22, 2026', 'players' => 32, 'prize' => '₹60,000', 'status' => 'Upcoming', 'progress' => 4],
            ['id' => 'ranking-series', 'name' => 'National Ranking Series — Leg 2', 'tag' => 'National', 'venue' => 'Chennai SDAT', 'dates' => 'Apr 10, 2026 - Apr 15, 2026', 'players' => 200, 'prize' => '₹12,00,000', 'status' => 'Completed', 'progress' => 100],
        ];
    }

    public static function courtBookings(): array
    {
        return [
            ['time' => '08:00', 'court_1' => 'available', 'court_2' => 'available', 'court_3' => 'available', 'court_4' => 'available', 'court_5' => 'available', 'court_6' => 'available'],
            ['time' => '09:00', 'court_1' => ['booked', 'Priya Sharma', 'State Open 2026'], 'court_2' => ['booked', 'Ananya Roy', ''], 'court_3' => 'available', 'court_4' => 'available', 'court_5' => ['booked', 'Divya Chopra', 'State Open 2026'], 'court_6' => 'available'],
            ['time' => '10:00', 'court_1' => ['booked', 'Dev Khanna', 'State Open 2026'], 'court_2' => 'available', 'court_3' => 'available', 'court_4' => ['booked', 'Layla Hassan', ''], 'court_5' => 'available', 'court_6' => 'available'],
            ['time' => '11:00', 'court_1' => 'available', 'court_2' => 'available', 'court_3' => 'available', 'court_4' => ['booked', 'Sita Patil', ''], 'court_5' => 'available', 'court_6' => ['booked', 'Meera Joshi', '']],
            ['time' => '12:00', 'court_1' => 'available', 'court_2' => 'available', 'court_3' => ['booked', 'Rohan Mehta', 'Club League Q2'], 'court_4' => 'available', 'court_5' => 'available', 'court_6' => 'available'],
            ['time' => '13:00', 'court_1' => 'available', 'court_2' => 'available', 'court_3' => ['booked', 'Vikram Nair', 'Club League Q2'], 'court_4' => 'available', 'court_5' => 'available', 'court_6' => 'available'],
            ['time' => '14:00', 'court_1' => 'available', 'court_2' => ['maintenance', 'Maintenance', ''], 'court_3' => 'available', 'court_4' => 'available', 'court_5' => 'available', 'court_6' => 'available'],
            ['time' => '15:00', 'court_1' => 'available', 'court_2' => 'available', 'court_3' => 'available', 'court_4' => ['booked', 'Arun Tiwari', ''], 'court_5' => 'available', 'court_6' => 'available'],
            ['time' => '16:00', 'court_1' => 'available', 'court_2' => 'available', 'court_3' => 'available', 'court_4' => 'available', 'court_5' => ['maintenance', 'Maintenance', ''], 'court_6' => 'available'],
            ['time' => '18:00', 'court_1' => 'available', 'court_2' => 'available', 'court_3' => 'available', 'court_4' => 'available', 'court_5' => 'available', 'court_6' => ['booked', 'Dev Khanna', '']],
        ];
    }

    public static function betting(): array
    {
        return [
            ['id' => 'BT-901', 'match' => 'Priya Sharma vs Ananya Roy', 'player1_name' => 'Priya Sharma', 'player2_name' => 'Ananya Roy', 'event' => 'State Open 2026', 'time' => '09:00', 'date' => 'Jun 4, 2026', 'odds_a' => 1.45, 'odds_b' => 2.80, 'pool' => 191000, 'bettor_count' => 490, 'status' => 'Open', 'state' => 'open', 'a_percent' => 65, 'b_percent' => 35, 'player1_pool' => 124150, 'player2_pool' => 66850, 'commission' => 9550, 'potential_payout' => 181450, 'is_manual' => true],
            ['id' => 'BT-902', 'match' => 'Dev Khanna vs Vikram Nair', 'player1_name' => 'Dev Khanna', 'player2_name' => 'Vikram Nair', 'event' => 'State Open 2026', 'time' => '10:30', 'date' => 'Jun 4, 2026', 'odds_a' => 1.30, 'odds_b' => 3.40, 'pool' => 270000, 'bettor_count' => 634, 'status' => 'Open', 'state' => 'open', 'a_percent' => 73, 'b_percent' => 27, 'player1_pool' => 197100, 'player2_pool' => 72900, 'commission' => 13500, 'potential_payout' => 256500, 'is_manual' => true],
            ['id' => 'BT-903', 'match' => 'Sita Patil vs Meera Joshi', 'player1_name' => 'Sita Patil', 'player2_name' => 'Meera Joshi', 'event' => 'Club League Q2', 'time' => '12:00', 'date' => 'Jun 4, 2026', 'odds_a' => 1.70, 'odds_b' => 2.10, 'pool' => 183000, 'bettor_count' => 438, 'status' => 'Live', 'state' => 'live', 'a_percent' => 58, 'b_percent' => 42, 'player1_pool' => 106140, 'player2_pool' => 76860, 'commission' => 9150, 'potential_payout' => 173850, 'is_manual' => false],
            ['id' => 'BT-904', 'match' => 'Rohan Mehta vs Arun Tiwari', 'player1_name' => 'Rohan Mehta', 'player2_name' => 'Arun Tiwari', 'event' => 'Club League Q2', 'time' => '14:00', 'date' => 'Jun 4, 2026', 'odds_a' => 1.55, 'odds_b' => 2.50, 'pool' => 69000, 'bettor_count' => 165, 'status' => 'Suspended', 'state' => 'suspended', 'a_percent' => 44, 'b_percent' => 56, 'player1_pool' => 30360, 'player2_pool' => 38640, 'commission' => 3450, 'potential_payout' => 65550, 'is_manual' => true],
            ['id' => 'BT-905', 'match' => 'Layla Hassan vs Divya Chopra', 'player1_name' => 'Layla Hassan', 'player2_name' => 'Divya Chopra', 'event' => 'State Open 2026', 'time' => '15:00', 'date' => 'Jun 4, 2026', 'odds_a' => 1.90, 'odds_b' => 1.95, 'pool' => 220000, 'bettor_count' => 521, 'status' => 'Settled', 'state' => 'settled', 'a_percent' => 52, 'b_percent' => 48, 'player1_pool' => 114400, 'player2_pool' => 105600, 'commission' => 11000, 'potential_payout' => 209000, 'is_manual' => false],
            ['id' => 'BT-906', 'match' => 'Meera Joshi vs Ananya Roy', 'player1_name' => 'Meera Joshi', 'player2_name' => 'Ananya Roy', 'event' => 'Evening Ladder', 'time' => '18:30', 'date' => 'Jun 4, 2026', 'odds_a' => 2.25, 'odds_b' => 1.62, 'pool' => 146000, 'bettor_count' => 302, 'status' => 'Open', 'state' => 'open', 'a_percent' => 39, 'b_percent' => 61, 'player1_pool' => 56940, 'player2_pool' => 89060, 'commission' => 7300, 'potential_payout' => 138700, 'is_manual' => false],
            ['id' => 'BT-907', 'match' => 'Priya Sharma vs Layla Hassan', 'player1_name' => 'Priya Sharma', 'player2_name' => 'Layla Hassan', 'event' => 'Women\'s Singles', 'time' => '19:15', 'date' => 'Jun 4, 2026', 'odds_a' => 1.72, 'odds_b' => 2.18, 'pool' => 118000, 'bettor_count' => 244, 'status' => 'Open', 'state' => 'open', 'a_percent' => 60, 'b_percent' => 40, 'player1_pool' => 70800, 'player2_pool' => 47200, 'commission' => 5900, 'potential_payout' => 112100, 'is_manual' => true],
            ['id' => 'BT-908', 'match' => 'Vikram Nair vs Rohan Mehta', 'player1_name' => 'Vikram Nair', 'player2_name' => 'Rohan Mehta', 'event' => 'Men\'s Singles', 'time' => '20:00', 'date' => 'Jun 4, 2026', 'odds_a' => 1.88, 'odds_b' => 2.02, 'pool' => 94000, 'bettor_count' => 211, 'status' => 'Live', 'state' => 'live', 'a_percent' => 49, 'b_percent' => 51, 'player1_pool' => 46060, 'player2_pool' => 47940, 'commission' => 4700, 'potential_payout' => 89300, 'is_manual' => false],
        ];
    }

    public static function transactions(): array
    {
        return [
            ['id' => 'TXN-9901', 'user' => 'Nguyen Van A', 'ticket' => 'BT-901', 'match' => 'Priya vs Ananya', 'stake' => 500000, 'pick' => 'Priya Sharma', 'odds' => 1.45, 'potential' => 725000, 'status' => 'Pending', 'time' => '08:42'],
            ['id' => 'TXN-9900', 'user' => 'Tran Thi B', 'ticket' => 'BT-902', 'match' => 'Dev vs Vikram', 'stake' => 200000, 'pick' => 'Dev Khanna', 'odds' => 1.30, 'potential' => 260000, 'status' => 'Pending', 'time' => '08:38'],
            ['id' => 'TXN-9899', 'user' => 'Le Van C', 'ticket' => 'BT-905', 'match' => 'Layla vs Divya', 'stake' => 1000000, 'pick' => 'Layla Hassan', 'odds' => 1.90, 'potential' => 1900000, 'status' => 'Won', 'time' => 'Yesterday'],
            ['id' => 'TXN-9898', 'user' => 'Pham Thi D', 'ticket' => 'BT-905', 'match' => 'Layla vs Divya', 'stake' => 300000, 'pick' => 'Divya Chopra', 'odds' => 1.95, 'potential' => 585000, 'status' => 'Lost', 'time' => 'Yesterday'],
            ['id' => 'TXN-9897', 'user' => 'Hoang Van E', 'ticket' => 'BT-902', 'match' => 'Dev vs Vikram', 'stake' => 150000, 'pick' => 'Vikram Nair', 'odds' => 3.40, 'potential' => 510000, 'status' => 'Pending', 'time' => '08:21'],
            ['id' => 'TXN-9896', 'user' => 'Nguyen Thi F', 'ticket' => 'BT-903', 'match' => 'Sita vs Meera', 'stake' => 400000, 'pick' => 'Meera Joshi', 'odds' => 2.10, 'potential' => 840000, 'status' => 'Pending', 'time' => '08:15'],
            ['id' => 'TXN-9895', 'user' => 'Bui Quang G', 'ticket' => 'BT-906', 'match' => 'Meera vs Ananya', 'stake' => 250000, 'pick' => 'Ananya Roy', 'odds' => 1.62, 'potential' => 405000, 'status' => 'Pending', 'time' => '08:07'],
            ['id' => 'TXN-9894', 'user' => 'Dang Minh H', 'ticket' => 'BT-907', 'match' => 'Priya vs Layla', 'stake' => 750000, 'pick' => 'Priya Sharma', 'odds' => 1.72, 'potential' => 1290000, 'status' => 'Pending', 'time' => '08:01'],
            ['id' => 'TXN-9893', 'user' => 'Vo Thanh I', 'ticket' => 'BT-908', 'match' => 'Vikram vs Rohan', 'stake' => 180000, 'pick' => 'Rohan Mehta', 'odds' => 2.02, 'potential' => 363600, 'status' => 'Pending', 'time' => '07:52'],
            ['id' => 'TXN-9892', 'user' => 'Doan Kim J', 'ticket' => 'BT-901', 'match' => 'Priya vs Ananya', 'stake' => 320000, 'pick' => 'Ananya Roy', 'odds' => 2.80, 'potential' => 896000, 'status' => 'Pending', 'time' => '07:44'],
        ];
    }

    public static function scheduleMonth(): Carbon
    {
        return Carbon::create(2026, 6, 1);
    }
}
