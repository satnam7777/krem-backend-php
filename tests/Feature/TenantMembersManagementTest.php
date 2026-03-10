<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\TenantMembership;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TenantMembersManagementTest extends TenancyTestCase
{
    public function test_owner_can_add_list_and_remove_members(): void
    {
        [$tenant, $db] = $this->provisionTenant('Org', 'org.local');

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@org.local',
            'password' => bcrypt('password'),
        ]);
        $this->centralMembership($tenant->id, $owner->id, 'OWNER');

        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@org.local',
            'password' => bcrypt('password'),
        ]);

        $this->useTenantDb($db);
        $salon = Salon::create(['name' => 'Salon', 'status' => 'active']);
        SalonMember::create(['salon_id' => $salon->id, 'user_id' => $owner->id, 'role' => 'OWNER', 'status' => 'active']);

        Sanctum::actingAs($owner, ['*']);

        $this->withHeaders(['Host' => 'org.local'])
            ->putJson('/api/tenant/members', ['email' => 'staff@org.local', 'role' => 'STAFF'])
            ->assertOk()
            ->assertJsonFragment(['user_id' => $staff->id]);

        $this->withHeaders(['Host' => 'org.local'])
            ->getJson('/api/tenant/members')
            ->assertOk()
            ->assertSee('staff@org.local');

        $this->withHeaders(['Host' => 'org.local'])
            ->deleteJson('/api/tenant/members/'.$staff->id)
            ->assertOk()
            ->assertJsonFragment(['deleted' => 1]);

        $this->assertNull(TenantMembership::where('tenant_id',$tenant->id)->where('user_id',$staff->id)->first());
    }

    public function test_non_owner_cannot_manage_members(): void
    {
        [$tenant, $db] = $this->provisionTenant('Org2', 'org2.local');

        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@org2.local',
            'password' => bcrypt('password'),
        ]);
        $this->centralMembership($tenant->id, $staff->id, 'STAFF');

        $this->useTenantDb($db);
        $salon = Salon::create(['name' => 'Salon', 'status' => 'active']);
        SalonMember::create(['salon_id' => $salon->id, 'user_id' => $staff->id, 'role' => 'STAFF', 'status' => 'active']);

        Sanctum::actingAs($staff, ['*']);

        $this->withHeaders(['Host' => 'org2.local'])
            ->getJson('/api/tenant/members')
            ->assertStatus(403);
    }
}
