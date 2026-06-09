<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        return view('auth.forgot-password', [
            'email' => $request->query('email', Auth::user()?->email),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($validated);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'A password reset link has been sent to your email.')
            : back()->withErrors(['email' => $this->statusMessage($status)])->onlyInput('email');
    }

    public function edit(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => $this->statusMessage($status)])
                ->withInput($request->only('email'));
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->with('success', 'Password reset successfully. Please sign in with your new password.');
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'We could not find an account with that email address.',
            Password::INVALID_TOKEN => 'This password reset link is invalid or has expired.',
            Password::RESET_THROTTLED => 'Please wait before requesting another password reset link.',
            default => 'Unable to reset the password. Please try again.',
        };
    }
}
