<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayersController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'rank');
        $dir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowed = ['name', 'category', 'club', 'rank', 'wins', 'losses', 'rating', 'status'];
        $sort = in_array($sort, $allowed, true) ? $sort : 'rank';
        $search = $request->query('search', '');
        $columnMap = ['rating' => 'elo_rating', 'status' => 'is_banned', 'category' => 'rank', 'club' => 'id'];

        $users = User::query()
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
            $status = $user->is_banned ? 'Banned' : ($user->role === 'admin' ? 'Admin' : 'Active');

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
                'status' => $status,
                'role' => $user->role,
                'posts_count' => $user->posts_count,
                'bets_count' => $user->bets_count,
                'is_banned' => $user->is_banned,
                'can_manage' => auth()->id() !== $user->id,
            ];
        })->all();

        if (empty($players)) {
            $players = AdminMockData::players();
        }

        return view('admin.players', compact('players', 'sort', 'dir', 'search'));
    }

    public function create()
    {
        return view('admin.players-create');
    }

    public function store(Request $request)
    {
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

    public function ban(Request $request, User $user)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự ban tài khoản của mình.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->where('is_banned', false)->count() <= 1) {
            return back()->with('error', 'Không thể ban admin cuối cùng đang hoạt động.');
        }

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $data['reason'] ?? 'Banned by admin',
        ]);

        return back()->with('success', 'Đã ban user.');
    }

    public function unban(User $user)
    {
        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        return back()->with('success', 'Đã mở ban user.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản của mình.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Không thể xóa admin cuối cùng.');
        }

        $user->delete();

        return redirect()->route('admin.players')->with('success', 'Đã xóa user và dữ liệu liên quan.');
    }
}
