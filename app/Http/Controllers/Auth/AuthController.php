<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Si l'utilisateur est déjà connecté, le rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect based on user role
            $user = Auth::user();
            
            // Check if user needs to complete profile
            if ($user->needsProfileCompletion()) {
                return redirect()->route('profile.complete');
            }
            
            // Redirect teachers to their dashboard
            if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'super-admin'])) {
                return redirect()->route('teacher.dashboard');
            }
            
            // Redirect students to their dashboard (when implemented)
            if ($user->hasRole('student') && !$user->hasAnyRole(['admin', 'super-admin', 'teacher'])) {
                return redirect()->route('admin.dashboard'); // TODO: student.dashboard
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
