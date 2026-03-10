<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class RateLimitTest extends TenancyTestCase
{
    public function test_api_rate_limit_returns_429_after_threshold(): void
    {
        config(['krema_hardening.rate_limits.api_per_minute' => 2]);

        [$tenant, $db] = $this->provisionTenant('Rate', 'rate.local');

        $this->centralMembership($tenant->id, $owner->id, 'OWNER');

        $this->useTenantDb($db);

        $salon = Salon::create(['name' => 'Rate Salon', 'status' => 'active']);

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@rate.local',
            'password' => bcrypt('password'),
        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $user->id,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        Client::create([
            'salon_id' => $salon->id,
            'first_name' => 'Ana',
            'last_name' => 'Test',
            'phone' => '555-666',
            'email' => 'ana@rate.local',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user, ['*']);

        $headers = ['Host' => 'rate.local', 'X-Salon-Id' => (string)$salon->id];

        $this->withHeaders($headers)->getJson('/api/clients')->assertOk();
        $this->withHeaders($headers)->getJson('/api/clients')->assertOk();

        $this->withHeaders($headers)->getJson('/api/clients')->assertStatus(429);
    }
}
