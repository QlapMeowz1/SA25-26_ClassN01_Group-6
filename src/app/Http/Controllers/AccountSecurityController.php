<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountSecurityController extends Controller
{
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        Auth::logoutOtherDevices($validated['current_password']);

        return back()->with('success', 'Password updated. Other authenticated sessions were signed out.');
    }

    public function logoutOtherDevices(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($validated['current_password']);

        return back()->with('success', 'Other devices have been signed out.');
    }
}
