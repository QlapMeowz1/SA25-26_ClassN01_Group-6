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
        $columnMap = ['rating' => 'elo_rating', 'status' => 'role', 'category' => 'rank', 'club' => 'id'];

        $users = User::query()
            ->orderBy($columnMap[$sort] ?? $sort, $dir)
            ->get();

        $players = $users->map(function (User $user, int $index) {
            return [
                'id' => $user->id,
                'initials' => collect(explode(' ', $user->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode(''),
                'name' => $user->name,
                'category' => $user->rank ?? 'Beginner',
                'club' => $user->teams()->value('name') ?? 'Independent',
                'rank' => $index + 1,
                'wins' => $user->wins ?? 0,
                'losses' => $user->losses ?? 0,
                'rating' => $user->elo_rating ?? 0,
                'status' => $user->role === 'admin' ? 'Active' : 'Active',
            ];
        })->all();

        if (empty($players)) {
            $players = AdminMockData::players();
        }

        return view('admin.players', compact('players', 'sort', 'dir'));
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
}
