<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class InvitationController extends Controller
{
    /**
     * Show the invitation acceptance form
     */
    public function show($token)
    {
        $invitation = UserInvitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Invitation non trouvée.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'Cette invitation a expiré.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'Cette invitation a déjà été acceptée.');
        }

        // Check if user already exists
        $existingUser = User::where('email', $invitation->email)->first();
        if ($existingUser) {
            return redirect()->route('login')
                ->with('error', 'Un compte existe déjà avec cette adresse email.');
        }

        return Inertia::render('Auth/AcceptInvitation', [
            'invitation' => [
                'email' => $invitation->email,
                'role' => $invitation->role,
                'token' => $invitation->token,
            ],
        ]);
    }

    /**
     * Accept the invitation and create user account
     */
    public function accept(Request $request, $token)
    {
        $invitation = UserInvitation::where('token', $token)->first();

        if (!$invitation || $invitation->isExpired() || $invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'token' => 'Invitation invalide ou expirée.',
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $invitation->email,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'profile_completed' => true,
            'invitation_token' => $token,
        ]);

        // Assign role
        $user->assignRole($invitation->role);

        // Mark invitation as accepted
        $invitation->update([
            'accepted_at' => now(),
        ]);

        // Auto-login the user
        auth()->login($user);

        // If teacher role, redirect to class selection
        if ($invitation->role === 'teacher') {
            return redirect()->route('profile.setup.classes')
                ->with('success', 'Compte créé avec succès ! Veuillez sélectionner vos classes.');
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Compte créé avec succès ! Bienvenue sur Evaly.');
    }

    /**
     * Send invitation email
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|exists:roles,name',
        ]);

        // Check if invitation already exists
        $existingInvitation = UserInvitation::where('email', $validated['email'])
            ->where('accepted_at', null)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            throw ValidationException::withMessages([
                'email' => 'Une invitation est déjà en attente pour cette adresse email.',
            ]);
        }

        // Create invitation
        $invitation = UserInvitation::create([
            'email' => $validated['email'],
            'token' => UserInvitation::generateToken(),
            'role' => $validated['role'],
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7), // 7 days to accept
        ]);

        // TODO: Send email notification
        // Mail::to($invitation->email)->send(new InvitationMail($invitation));

        return back()->with('success', 'Invitation envoyée avec succès !');
    }
}