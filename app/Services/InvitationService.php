<?php

namespace App\Services;

use App\Models\UserInvitation;
use Illuminate\Support\Facades\Log;

class InvitationService
{
    public function createInvitation(string $email, string $role, int $invitedBy)
    {
        try {
            $invitation = UserInvitation::create([
                'email' => $email,
                'token' => UserInvitation::generateToken(),
                'role' => $role,
                'invited_by' => $invitedBy,
                'expires_at' => now()->addDays(7),
            ]);

            Log::info('Invitation created', [
                'email' => $email,
                'role' => $role,
                'invited_by' => $invitedBy
            ]);

            return $invitation;
        } catch (\Exception $e) {
            Log::error('Error creating invitation', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getPendingInvitations()
    {
        return UserInvitation::with('inviter')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }

    public function getInvitationByToken(string $token)
    {
        return UserInvitation::where('token', $token)->first();
    }

    public function acceptInvitation(string $token)
    {
        try {
            $invitation = $this->getInvitationByToken($token);
            
            if (!$invitation) {
                throw new \Exception('Invitation not found');
            }

            $invitation->update(['accepted_at' => now()]);

            Log::info('Invitation accepted', [
                'email' => $invitation->email,
                'token' => $token
            ]);

            return $invitation;
        } catch (\Exception $e) {
            Log::error('Error accepting invitation', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

