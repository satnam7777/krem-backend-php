<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class NotificationsQueueSmokeTest extends TenancyTestCase
{
    public function test_notifications_health_or_skip(): void
    {
        [, $db] = $this->provisionTenant('Notify', 'n.local');
        $this->useTenantDb($db);

        $salon = Salon::create(['name' => 'Notify Salon', 'status' => 'active']);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@n.local',
            'password' => bcrypt('password'),        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $owner->id,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        Sanctum::actingAs($owner, ['*']);
        $headers = ['Host' => 'n.local', 'X-Salon-Id' => (string)$salon->id];

        // Prefer a simple health endpoint if exists.
        $r = $this->withHeaders($headers)->getJson('/api/notifications/health');
        $r->assertOk();
    }
}
