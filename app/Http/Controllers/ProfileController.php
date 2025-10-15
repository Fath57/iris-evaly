<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'menu_layout' => 'required|in:horizontal,vertical',
        ]);

        $request->user()->update($validated);

        return back();
    }
}