<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\JoinRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $category = (string) $request->query('category', 'all');
        $search = trim((string) $request->query('q', ''));
        $allowedCategories = ['all', 'interactions', 'matches', 'betting', 'system'];
        $category = in_array($category, $allowedCategories, true) ? $category : 'all';

        $notifications = $user->notifications()
            ->when($category !== 'all', function ($query) use ($category) {
                $types = $this->typesForCategory($category);

                if ($category === 'system') {
                    $query->whereNotIn('type', $this->categorizedTypes());
                } else {
                    $query->whereIn('type', $types);
                }
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $notificationPreference = $user->notificationPreference()->firstOrCreate([]);

        return view('notifications.index', compact('notifications', 'notificationPreference', 'category', 'search'));
    }

    public function recent(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['notifications' => []]);
        }

        $preferences = $user->notificationPreference()->firstOrCreate([]);
        $items = $user->notifications()
            ->orderByDesc('is_pinned')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (Notification $n) use ($user) {
                $category = $this->resolveNotificationCategory($n->type);

                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'category' => $category,
                    'data' => [
                        'title' => $n->title,
                        'message' => $n->message,
                    ],
                    'is_read' => (bool) $n->is_read,
                    'is_pinned' => (bool) $n->is_pinned,
                    'time' => $n->created_at ? $n->created_at->diffForHumans() : null,
                    'link' => $this->resolveNotificationLink($n, $user->id),
                    'icon' => $this->resolveNotificationIcon($n->type),
                    'tone' => $this->resolveNotificationTone($n->type),
                    'actions' => $this->resolveNotificationActions($n, $user),
                ];
            })
            ->filter(function (array $item) use ($preferences) {
                $preference = $item['category'] . '_web';

                return !isset($preferences->{$preference}) || (bool) $preferences->{$preference};
            })
            ->values();

        if (config('app.demo_data') && $items->isEmpty()) {
            $demo = collect([
                ['id' => 'demo-1', 'type' => 'challenge', 'category' => 'matches', 'data' => ['title' => 'ShuttleKing challenged you to a match!', 'message' => 'ShuttleKing challenged you to a match!'], 'is_read' => true, 'time' => '5m ago', 'link' => route('challenges.index'), 'icon' => '⚔️', 'tone' => 'pending', 'actions' => []],
                ['id' => 'demo-2', 'type' => 'tournament', 'category' => 'system', 'data' => ['title' => "Tournament 'Spring Championship 2026' starts in 2 days", 'message' => "Tournament 'Spring Championship 2026' starts in 2 days"], 'is_read' => true, 'time' => '2h ago', 'link' => route('tournaments.index'), 'icon' => '🏆', 'actions' => []],
                ['id' => 'demo-3', 'type' => 'bet_won', 'category' => 'betting', 'data' => ['title' => 'Bet Won', 'message' => 'You won 725 coins from Priya vs Ananya.'], 'is_read' => true, 'time' => '1d ago', 'link' => route('bets.index'), 'icon' => '✅', 'tone' => 'win', 'actions' => []],
                ['id' => 'demo-4', 'type' => 'team', 'category' => 'system', 'data' => ['title' => 'SaigonSmashers invited you to join their team', 'message' => 'Join invite'], 'is_read' => true, 'time' => '2d ago', 'link' => route('teams.index'), 'icon' => '👥', 'actions' => []],
                ['id' => 'demo-5', 'type' => 'achievement', 'category' => 'interactions', 'data' => ['title' => "You earned 'First Win' achievement! 🏆", 'message' => "You earned 'First Win' achievement! 🏆"], 'is_read' => true, 'time' => '3d ago', 'link' => route('profile.show', $user->id), 'icon' => '🎉', 'actions' => []],
            ]);

            return response()->json([
                'notifications' => $demo,
                'unread_count' => 0,
            ]);
        }

        return response()->json([
            'notifications' => $items,
            'unread_count' => $user->notifications()->where('is_read', false)->count(),
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        $user = Auth::user();
        if (! $user || (int) $notification->user_id !== (int) $user->id) {
            return response()->json(['ok' => false], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        $updated = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'ok' => true,
            'updated' => $updated,
            'unread_count' => 0,
        ]);
    }

    public function markUnread(Request $request, Notification $notification)
    {
        $this->authorizeNotification($notification);
        $notification->update(['is_read' => false]);

        return response()->json(['ok' => true, 'is_read' => false]);
    }

    public function togglePin(Request $request, Notification $notification)
    {
        $this->authorizeNotification($notification);
        $notification->update(['is_pinned' => ! $notification->is_pinned]);

        return response()->json([
            'ok' => true,
            'is_pinned' => (bool) $notification->is_pinned,
        ]);
    }

    public function destroy(Request $request, Notification $notification)
    {
        $this->authorizeNotification($notification);
        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function clearRead(Request $request)
    {
        $deleted = $request->user()->notifications()->where('is_read', true)->delete();

        return back()->with('success', "{$deleted} read notifications deleted.");
    }

    public function updatePreferences(Request $request)
    {
        $fields = [
            'interactions_web',
            'matches_web',
            'betting_web',
            'system_web',
            'critical_email',
            'match_reminders',
            'betting_updates',
        ];
        $validated = $request->validate(
            collect($fields)->mapWithKeys(fn ($field) => [$field => ['nullable', 'boolean']])->all()
        );
        $values = collect($fields)->mapWithKeys(fn ($field) => [$field => $request->boolean($field)])->all();

        $request->user()->notificationPreference()->updateOrCreate([], $values);

        return back()->with('success', 'Notification preferences updated.');
    }

    public function categoryFor(Notification $notification): string
    {
        return $this->resolveNotificationCategory($notification->type);
    }

    private function resolveNotificationLink(Notification $notification, int $userId): string
    {
        if ($notification->target_url) {
            return $notification->target_url;
        }

        return match ($notification->type) {
            'challenge' => route('challenges.index') . '#received-challenges',
            'bet_placed', 'bet_won', 'bet_lost', 'wallet_credit', 'wallet_debit', 'wallet_low', 'odds_changed', 'betting_cancelled' => route('bets.index'),
            'match_request', 'match', 'result' => route('matches.index'),
            'like', 'comment' => route('posts.index'),
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
            'bet_placed' => '🎫',
            'bet_won', 'wallet_credit' => '💰',
            'wallet_debit' => '↘',
            'bet_lost' => '↘',
            'wallet_low' => '⚠️',
            'odds_changed' => '↕',
            'betting_cancelled' => '⏸',
            'tournament' => '🏆',
            'team' => '👥',
            'achievement' => '🎉',
            default => '🔔',
        };
    }

    private function authorizeNotification(Notification $notification): void
    {
        abort_unless(
            Auth::check() && (int) $notification->user_id === (int) Auth::id(),
            403
        );
    }

    private function resolveNotificationCategory(?string $type): string
    {
        return match ($type) {
            'like', 'comment', 'mention', 'follow', 'friend_request' => 'interactions',
            'challenge', 'match_request', 'match', 'result' => 'matches',
            'bet_placed', 'bet_won', 'bet_lost', 'wallet_credit', 'wallet_debit', 'wallet_low', 'odds_changed', 'betting_cancelled' => 'betting',
            default => 'system',
        };
    }

    private function typesForCategory(string $category): array
    {
        return match ($category) {
            'interactions' => ['like', 'comment', 'mention', 'follow', 'friend_request'],
            'matches' => ['challenge', 'match_request', 'match', 'result'],
            'betting' => ['bet_placed', 'bet_won', 'bet_lost', 'wallet_credit', 'wallet_debit', 'wallet_low', 'odds_changed', 'betting_cancelled'],
            default => [],
        };
    }

    private function categorizedTypes(): array
    {
        return array_merge(
            $this->typesForCategory('interactions'),
            $this->typesForCategory('matches'),
            $this->typesForCategory('betting')
        );
    }

    private function resolveNotificationTone(?string $type): string
    {
        return match ($type) {
            'bet_won', 'wallet_credit' => 'win',
            'bet_lost', 'wallet_debit', 'betting_cancelled' => 'loss',
            'bet_placed', 'odds_changed', 'wallet_low' => 'pending',
            default => 'neutral',
        };
    }

    private function resolveNotificationActions(Notification $notification, $user): array
    {
        if ($notification->type !== 'challenge') {
            return [];
        }

        $directChallenge = Challenge::where('opponent_id', $user->id)
            ->where('status', 'pending')
            ->when($notification->related_user_id, fn ($query) => $query->where('challenger_id', $notification->related_user_id))
            ->latest()
            ->first();

        if ($directChallenge) {
            return [
                ['label' => 'Accept', 'method' => 'POST', 'url' => route('challenges.accept', $directChallenge->id), 'tone' => 'accept'],
                ['label' => 'Reject', 'method' => 'POST', 'url' => route('challenges.reject', $directChallenge->id), 'tone' => 'reject'],
            ];
        }

        $joinRequest = JoinRequest::where('requestable_type', Challenge::class)
            ->where('requester_id', $notification->related_user_id)
            ->where('status', 'pending')
            ->whereHasMorph('requestable', [Challenge::class], function ($query) use ($user) {
                $query->where('challenger_id', $user->id)->where('status', 'open');
            })
            ->latest()
            ->first();

        if ($joinRequest && $joinRequest->requestable instanceof Challenge) {
            return [
                ['label' => 'Accept', 'method' => 'POST', 'url' => route('challenges.requests.accept', [$joinRequest->requestable->id, $joinRequest->id]), 'tone' => 'accept'],
                ['label' => 'Reject', 'method' => 'POST', 'url' => route('challenges.requests.reject', [$joinRequest->requestable->id, $joinRequest->id]), 'tone' => 'reject'],
            ];
        }

        return [];
    }
}
