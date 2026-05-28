<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    /**
     * Update user theme preference
     */
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark,system',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->update(['theme' => $request->theme]);

        return response()->json([
            'success' => true,
            'theme' => $user->theme,
            'message' => 'Theme updated successfully',
        ]);
    }

    /**
     * Get user theme preference
     */
    public function get(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['theme' => 'dark'], 200);
        }

        return response()->json([
            'theme' => $user->theme ?? 'dark',
        ]);
    }
}
