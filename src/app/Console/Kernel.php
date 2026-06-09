<?php

namespace App\Console;

use App\Models\GameMatch;
use App\Models\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            GameMatch::with(['player1.notificationPreference', 'player2.notificationPreference'])
                ->where('status', 'scheduled')
                ->whereBetween('match_date', [now()->addMinutes(60), now()->addMinutes(120)])
                ->get()
                ->each(function (GameMatch $match) {
                    foreach ([$match->player1, $match->player2] as $player) {
                        if (!$player || $player->notificationPreference?->match_reminders === false) {
                            continue;
                        }

                        $targetUrl = route('matches.show', $match->id);
                        $alreadySent = Notification::where('user_id', $player->id)
                            ->where('title', 'Match Reminder')
                            ->where('target_url', $targetUrl)
                            ->where('created_at', '>=', now()->subHours(3))
                            ->exists();

                        if (!$alreadySent) {
                            Notification::create([
                                'user_id' => $player->id,
                                'title' => 'Match Reminder',
                                'message' => 'Your match starts ' . $match->match_date->diffForHumans() . ' at ' . ($match->location ?: 'Court TBD') . '.',
                                'type' => 'match',
                                'target_url' => $targetUrl,
                            ]);
                        }
                    }
                });
        })->name('send-upcoming-match-reminders')->everyFifteenMinutes()->withoutOverlapping();

        $schedule->call(function () {
            DB::table('password_reset_tokens')
                ->where('created_at', '<', now()->subMinutes((int) config('auth.passwords.users.expire', 15)))
                ->delete();
        })->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
