<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class MembershipEnforcementTest extends TenancyTestCase
{
    public function test_me_salons_requires_membership_and_returns_salons(): void
    {
        [$tenant, $db] = $this->provisionTenant('Tenant', 't.local');

        $user = User::create([
            'name' => 'User',
            'email' => 'user@t.local',
            'password' => bcrypt('password'),
        ]);

        // No membership yet
        $this->useTenantDb($db);
        $salon = Salon::create(['name' => 'Salon 1', 'status' => 'active']);
        SalonMember::create(['salon_id' => $salon->id, 'user_id' => $user->id, 'role' => 'OWNER', 'status' => 'active']);

        Sanctum::actingAs($user, ['*']);

        $this->withHeaders(['Host' => 't.local'])->getJson('/api/me/salons')->assertStatus(403);

        // Add membership then it should work
        $this->centralMembership($tenant->id, $user->id, 'OWNER');

        $this->withHeaders(['Host' => 't.local'])->getJson('/api/me/salons')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Salon 1']);
    }
}
