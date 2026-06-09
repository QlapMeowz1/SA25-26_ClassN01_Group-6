<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\WalletService;
use App\Services\AuditService;

class PlayersController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'rank');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowed = ['name', 'category', 'club', 'rank', 'wins', 'losses', 'rating', 'wallet', 'status'];
        $sort = in_array($sort, $allowed, true) ? $sort : 'rank';
        $search = $request->query('search', '');
        $columnMap = ['rating' => 'elo_rating', 'wallet' => 'virtual_coins', 'status' => 'is_banned', 'category' => 'rank', 'club' => 'id'];

        $users = User::withTrashed()
            ->withCount(['posts', 'bets'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('rank', 'like', "%{$search}%");
                });
            })
            ->orderBy($columnMap[$sort] ?? $sort, $dir)
            ->get();

        $players = $users->map(function (User $user, int $index) {
            $status = $user->trashed() ? 'Deleted' : ($user->is_banned ? 'Banned' : ($user->hasAdminAccess() ? 'Admin' : 'Active'));

            return [
                'id' => $user->id,
                'initials' => collect(explode(' ', $user->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode(''),
                'name' => $user->name,
                'email' => $user->email,
                'category' => $user->rank ?? 'Beginner',
                'club' => $user->teams()->value('name') ?? 'Independent',
                'rank' => $index + 1,
                'wins' => $user->wins ?? 0,
                'losses' => $user->losses ?? 0,
                'rating' => $user->elo_rating ?? 0,
                'wallet' => (int) ($user->virtual_coins ?? 0),
                'status' => $status,
                'role' => $user->role,
                'posts_count' => $user->posts_count,
                'bets_count' => $user->bets_count,
                'is_banned' => $user->is_banned,
                'can_manage' => auth()->id() !== $user->id,
                'can_update_role' => auth()->id() !== $user->id,
                'deleted_at' => $user->deleted_at,
            ];
        })->all();

        if (config('app.demo_data') && empty($players)) {
            $players = AdminMockData::players();
        }

        return view('admin.players', compact('players', 'sort', 'dir', 'search'));
    }

    public function create()
    {
        $this->authorizeUserManagement();

        return view('admin.players-create');
    }

    public function store(Request $request)
    {
        $this->authorizeUserManagement();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'rank' => ['required', Rule::in(['Beginner', 'Intermediate', 'Advanced', 'Professional'])],
            'elo_rating' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'wins' => ['nullable', 'integer', 'min:0'],
            'losses' => ['nullable', 'integer', 'min:0'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'rank' => $data['rank'],
            'elo_rating' => $data['elo_rating'] ?? 1200,
            'wins' => $data['wins'] ?? 0,
            'losses' => $data['losses'] ?? 0,
            'virtual_coins' => 1000,
            'role' => 'user',
            'password' => Hash::make(str()->random(16)),
        ]);

        return redirect()->route('admin.players')->with('success', 'Đã thêm player mới.');
    }

    public function updateRole(Request $request, User $user, AuditService $audit)
    {
        $this->authorizeUserManagement();

        $data = $request->validate([
            'role' => ['required', Rule::in(['user', 'moderator', 'betting_manager', 'admin', 'super_admin'])],
        ]);

        if ($user->id === $request->user()->id && !in_array($data['role'], ['admin', 'super_admin'], true)) {
            return back()->with('error', 'Bạn không thể tự hạ quyền admin của mình.');
        }

        if ($user->hasAdminAccess() && !in_array($data['role'], ['admin', 'super_admin'], true)
            && User::whereIn('role', ['admin', 'super_admin'])->count() <= 1) {
            return back()->with('error', 'Không thể hạ quyền admin cuối cùng.');
        }

        $before = ['role' => $user->role];
        $user->update(['role' => $data['role']]);
        $audit->record('user.role_updated', $user, $before, ['role' => $data['role']]);

        return back()->with('success', 'Đã cập nhật role cho user.');
    }

    public function updateWallet(Request $request, User $user, WalletService $wallets, AuditService $audit)
    {
        $this->authorizeUserManagement();

        $data = $request->validate([
            'operation' => ['required', Rule::in(['set', 'add', 'subtract'])],
            'amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $before = (int) $user->virtual_coins;
        $amount = (int) $data['amount'];
        $target = match ($data['operation']) {
            'set' => $amount,
            'add' => min(1000000000, $before + $amount),
            'subtract' => max(0, $before - $amount),
        };
        $reason = trim((string) ($data['reason'] ?? 'Admin wallet adjustment'));
        $transaction = $wallets->set($user, $target, $reason, $request->user());
        $after = $transaction->balance_after;

        $difference = $after - $before;
        $direction = $difference >= 0 ? 'credited' : 'deducted';
        $audit->record('user.wallet_updated', $user, ['virtual_coins' => $before], ['virtual_coins' => $after], [
            'operation' => $data['operation'],
            'reason' => $reason,
            'wallet_transaction_id' => $transaction->id,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Wallet Balance Updated',
            'message' => sprintf(
                'An admin %s %s points. Your balance is now %s points.%s',
                $direction,
                number_format(abs($difference)),
                number_format($after),
                $reason !== '' ? ' Reason: ' . $reason : ''
            ),
            'type' => $difference >= 0 ? 'wallet_credit' : 'wallet_debit',
            'related_user_id' => $request->user()->id,
            'target_url' => route('profile.show', $user->id) . '#betting',
        ]);

        return back()->with('success', sprintf(
            'Updated %s wallet from %s to %s points.',
            $user->name,
            number_format($before),
            number_format($after)
        ));
    }

    public function ban(Request $request, User $user, AuditService $audit)
    {
        $this->authorizeUserManagement();

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự ban tài khoản của mình.');
        }

        if ($user->isAdmin() && User::whereIn('role', ['admin', 'super_admin'])->where('is_banned', false)->count() <= 1) {
            return back()->with('error', 'Không thể ban admin cuối cùng đang hoạt động.');
        }

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $data['reason'] ?? 'Banned by admin',
        ]);
        $audit->record('user.banned', $user, ['is_banned' => false], [
            'is_banned' => true,
            'reason' => $user->ban_reason,
        ]);

        return back()->with('success', 'Đã ban user.');
    }

    public function unban(User $user, AuditService $audit)
    {
        $this->authorizeUserManagement();

        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
        ]);
        $audit->record('user.unbanned', $user, ['is_banned' => true], ['is_banned' => false]);

        return back()->with('success', 'Đã mở ban user.');
    }

    public function destroy(Request $request, User $user, AuditService $audit)
    {
        $this->authorizeUserManagement();

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản của mình.');
        }

        if ($user->isAdmin() && User::whereIn('role', ['admin', 'super_admin'])->count() <= 1) {
            return back()->with('error', 'Không thể xóa admin cuối cùng.');
        }

        $audit->record('user.deleted', $user, $user->only(['name', 'email', 'role']), []);
        $user->delete();

        return redirect()->route('admin.players')->with('success', 'User moved to trash and can be restored.');
    }

    public function restore(int $user, AuditService $audit)
    {
        $this->authorizeUserManagement();

        $model = User::withTrashed()->findOrFail($user);
        $model->restore();
        $audit->record('user.restored', $model, [], $model->only(['name', 'email', 'role']));

        return back()->with('success', 'User restored.');
    }

    public function bulk(Request $request, AuditService $audit)
    {
        $this->authorizeUserManagement();

        $data = $request->validate([
            'action' => ['required', Rule::in(['ban', 'unban', 'delete'])],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $users = User::whereIn('id', $data['user_ids'])->where('id', '!=', $request->user()->id)->get();
        foreach ($users as $user) {
            if ($data['action'] === 'ban') {
                $user->update(['is_banned' => true, 'banned_at' => now(), 'ban_reason' => 'Bulk admin action']);
            } elseif ($data['action'] === 'unban') {
                $user->update(['is_banned' => false, 'banned_at' => null, 'ban_reason' => null]);
            } else {
                $user->delete();
            }
            $audit->record('user.bulk_' . $data['action'], $user);
        }

        return back()->with('success', count($users) . ' users updated.');
    }

    public function export(Request $request)
    {
        $this->authorizeUserManagement();

        $users = User::withTrashed()->orderBy('id')->get();

        return response()->streamDownload(function () use ($users) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Rank', 'ELO', 'Wallet', 'Banned', 'Deleted At']);
            foreach ($users as $user) {
                fputcsv($output, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->rank,
                    $user->elo_rating,
                    $user->virtual_coins,
                    $user->is_banned ? 'Yes' : 'No',
                    optional($user->deleted_at)->toDateTimeString(),
                ]);
            }
            fclose($output);
        }, 'players-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function authorizeUserManagement(): void
    {
        abort_unless(auth()->user()?->canManageUsers(), 403);
    }
}
