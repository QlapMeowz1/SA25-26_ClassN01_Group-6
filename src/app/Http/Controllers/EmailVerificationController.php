<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        $latestCode = EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        return view('auth.verify-email-code', compact('latestCode'));
    }

    public function verify(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $record = EmailVerificationCode::where('user_id', $user->id)
            ->where('email', $user->email)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (!$record || $record->expires_at->isPast()) {
            return back()->with('error', 'Verification code expired. Please request a new code.');
        }

        if ($record->attempts >= 5) {
            return back()->with('error', 'Too many failed attempts. Please request a new code.');
        }

        if (!Hash::check($validated['code'], $record->code_hash)) {
            $record->increment('attempts');

            return back()->withErrors([
                'code' => 'The verification code is incorrect.',
            ]);
        }

        $record->update(['consumed_at' => now()]);
        $user->forceFill(['email_verified_at' => now()])->save();

        EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        return redirect()->intended(route('dashboard'))->with('success', 'Email verified successfully.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        $latestCode = EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if ($latestCode && $latestCode->created_at->gt(now()->subMinute())) {
            return back()->with('error', 'Please wait a minute before requesting another code.');
        }

        $this->sendCode($user);

        return back()->with('success', 'A new verification code has been sent.');
    }

    public static function sendCode($user): void
    {
        EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100000, 999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw(
            "Your BadNet verification code is {$code}. It expires in 10 minutes.",
            function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Your BadNet verification code');
            }
        );
    }
}
