<?php

namespace App\Services;

use App\Models\SalonInvite;
use App\Models\SalonMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalonInviteService
{
    public function createInvite(int $salonId, string $email, string $role, int $actorId, int $ttlDays = 7): string
    {
        $rawToken = Str::random(48);

        SalonInvite::create([
            'salon_id' => $salonId,
            'email' => strtolower($email),
            'role' => $role,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => now()->addDays($ttlDays),
            'created_by' => $actorId,
        ]);

        return $rawToken;
    }

    public function acceptInvite(string $rawToken, ?string $password = null): User
    {
        return DB::transaction(function () use ($rawToken, $password) {
            $invite = SalonInvite::where('token_hash', hash('sha256', $rawToken))
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->firstOrFail();

            $user = User::where('email', $invite->email)->first();

            if (!$user) {
                if (!$password) {
                    abort(422, 'Password required for new user');
                }

                $user = User::create([
                    'email' => $invite->email,
                    'password' => Hash::make($password),
                ]);
            }

            SalonMember::firstOrCreate(
                ['salon_id' => $invite->salon_id, 'user_id' => $user->id],
                ['role' => $invite->role, 'status' => 'active', 'joined_at' => now()]
            );

            $invite->update(['accepted_at' => now()]);
            return $user;
        });
    }
}
