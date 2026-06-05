<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth()->guard($guard)->check()) {
                if (!auth()->guard($guard)->user()->email_verified_at) {
                    return redirect(route('verification.notice'));
                }

                return redirect(route('dashboard'));
            }
        }

        return $next($request);
    }
}
