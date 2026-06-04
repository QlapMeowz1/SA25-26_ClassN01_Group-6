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
            ['id' => 'BT-901', 'match' => 'Sita P. vs Meera J.', 'odds_a' => 1.62, 'odds_b' => 2.35, 'pool' => 18400, 'status' => 'Đang mở', 'a_percent' => 64, 'commission' => 920, 'potential_payout' => 29808],
            ['id' => 'BT-902', 'match' => 'Vikram N. vs Arun T.', 'odds_a' => 2.10, 'odds_b' => 1.82, 'pool' => 11200, 'status' => 'Tạm dừng', 'a_percent' => 42, 'commission' => 560, 'potential_payout' => 20384],
            ['id' => 'BT-903', 'match' => 'Dev K. vs Rohan M.', 'odds_a' => 1.44, 'odds_b' => 2.80, 'pool' => 25600, 'status' => 'Đã khóa', 'a_percent' => 78, 'commission' => 1280, 'potential_payout' => 36864],
        ];
    }

    public static function transactions(): array
    {
        return [
            ['id' => 'TX-7781', 'user' => 'Minh Anh', 'ticket' => 'BT-901', 'stake' => 1200, 'pick' => 'Sita P.', 'potential' => 1944, 'status' => 'Pending'],
            ['id' => 'TX-7782', 'user' => 'Huy Tran', 'ticket' => 'BT-901', 'stake' => 800, 'pick' => 'Meera J.', 'potential' => 1880, 'status' => 'Pending'],
            ['id' => 'TX-7783', 'user' => 'Linh Pham', 'ticket' => 'BT-902', 'stake' => 1500, 'pick' => 'Arun T.', 'potential' => 2730, 'status' => 'Review'],
        ];
    }

    public static function scheduleMonth(): Carbon
    {
        return Carbon::create(2026, 6, 1);
    }
}
