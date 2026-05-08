<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function recent(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['notifications' => []]);
        }

        $items = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'data' => [
                        'title' => $n->title,
                        'message' => $n->message,
                    ],
                    'is_read' => (bool) $n->is_read,
                    'time' => $n->created_at ? $n->created_at->diffForHumans() : null,
                    'link' => $this->resolveNotificationLink($n->type, $user->id),
                    'icon' => $this->resolveNotificationIcon($n->type),
                ];
            })->values();

        // If there are no real notifications, provide a small demo set to make the UI useful during development
        if ($items->isEmpty()) {
            $demo = collect([
                ['id' => 'demo-1', 'type' => 'challenge', 'data' => ['title' => 'ShuttleKing challenged you to a match!', 'message' => 'ShuttleKing challenged you to a match!'], 'is_read' => false, 'time' => '5m ago', 'link' => route('challenges.index'), 'icon' => '⚔️'],
                ['id' => 'demo-2', 'type' => 'tournament', 'data' => ['title' => "Tournament 'Spring Championship 2026' starts in 2 days", 'message' => "Tournament 'Spring Championship 2026' starts in 2 days"], 'is_read' => false, 'time' => '2h ago', 'link' => route('tournaments.index'), 'icon' => '🏆'],
                ['id' => 'demo-3', 'type' => 'match', 'data' => ['title' => 'Your match result: Won vs HanoiBirdies 21-18, 21-15', 'message' => 'You won!'], 'is_read' => true, 'time' => '1d ago', 'link' => route('matches.index'), 'icon' => '✅'],
                ['id' => 'demo-4', 'type' => 'team', 'data' => ['title' => 'SaigonSmashers invited you to join their team', 'message' => 'Join invite'], 'is_read' => false, 'time' => '2d ago', 'link' => route('teams.index'), 'icon' => '👥'],
                ['id' => 'demo-5', 'type' => 'achievement', 'data' => ['title' => "You earned 'First Win' achievement! 🏆", 'message' => "You earned 'First Win' achievement! 🏆"], 'is_read' => true, 'time' => '3d ago', 'link' => route('profile.show', $user->id), 'icon' => '🎉'],
            ]);

            return response()->json(['notifications' => $demo]);
        }

        return response()->json(['notifications' => $items]);
    }

    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    private function resolveNotificationLink(?string $type, int $userId): string
    {
        return match ($type) {
            'challenge' => route('challenges.index'),
            'tournament' => route('tournaments.index'),
            'team' => route('teams.index'),
            'achievement' => route('profile.show', $userId),
            default => route('matches.index'),
        };
    }

    private function resolveNotificationIcon(?string $type): string
    {
        return match ($type) {
            'challenge' => '⚔️',
            'tournament' => '🏆',
            'team' => '👥',
            'achievement' => '🎉',
            default => '🔔',
        };
    }
}
