<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GameMatch;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $matches = GameMatch::where('player1_id', $id)
                        ->orWhere('player2_id', $id)
                        ->where('status', 'completed')
                        ->latest()
                        ->limit(10)
                        ->get();

        $posts = $user->posts()->latest()->paginate(10);

        return view('profile.show', compact('user', 'matches', 'posts'));
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && file_exists(public_path('avatars/' . $user->avatar))) {
                unlink(public_path('avatars/' . $user->avatar));
            }

            $file = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('avatars'), $filename);
            $validated['avatar'] = $filename;
        }

        $user->update($validated);

        return redirect()->route('profile.show', $user->id)->with('success', 'Profile updated successfully!');
    }
}
