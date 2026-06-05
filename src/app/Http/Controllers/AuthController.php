<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmailVerificationController;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'elo_rating' => 1200,
            'virtual_coins' => 5000,
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        EmailVerificationController::sendCode($user);

        return redirect()->route('verification.notice')->with('success', 'Account created. Please verify your email.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (Auth::user()->isBanned()) {
                $reason = Auth::user()->ban_reason;
                Auth::logout();

                return back()->withErrors([
                    'email' => $reason
                        ? 'Your account has been banned: ' . $reason
                        : 'Your account has been banned.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            if (!Auth::user()->email_verified_at) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Logged in successfully!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully!');
    }
}
