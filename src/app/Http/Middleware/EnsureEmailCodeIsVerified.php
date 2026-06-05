<?php

namespace App\Http\Middleware;

use App\Http\Controllers\EmailVerificationController;
use Closure;
use Illuminate\Http\Request;

class EnsureEmailCodeIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->email_verified_at || $request->routeIs('verification.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        $hasActiveCode = $user->emailVerificationCodes()
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->exists();

        if (!$hasActiveCode) {
            EmailVerificationController::sendCode($user);
        }

        return redirect()->route('verification.notice');
    }
}
