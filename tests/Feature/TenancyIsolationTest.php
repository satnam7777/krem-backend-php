<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TenancyIsolationTest extends TenancyTestCase
{
    public function test_unknown_host_returns_404(): void
    {
        $res = $this->withHeader('Host', 'unknown.local')
            ->getJson('/api/clients');

        $res->assertStatus(404);
    }

    public function test_tenant_a_cannot_see_tenant_b_data(): void
    {
        [$tenantA, $dbA] = $this->provisionTenant('Salon A', 'a.local');
        [$tenantB, $dbB] = $this->provisionTenant('Salon B', 'b.local');

        $userA = User::create([
            'name' => 'A User',
            'email' => 'a.user@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->centralMembership($tenantA->id, $userA->id, 'OWNER');

        $userB = User::create([
            'name' => 'B User',
            'email' => 'b.user@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->centralMembership($tenantB->id, $userB->id, 'OWNER');

        $this->seedTenantBusiness($dbA, $userA->id, 'A Salon', 'A Client');
        $this->seedTenantBusiness($dbB, $userB->id, 'B Salon', 'B Client');

        Sanctum::actingAs($userA, ['*']);

        $salonAId = $this->firstSalonId($dbA);

        $res = $this->withHeaders([
            'Host' => 'a.local',
            'X-Salon-Id' => (string) $salonAId,
        ])->getJson('/api/clients');

        $res->assertOk();
        $this->assertStringContainsString('A Client', $res->getContent());
        $this->assertStringNotContainsString('B Client', $res->getContent());
    }

    private function seedTenantBusiness(string $dbName, int $userId, string $salonName, string $clientName): void
    {
        $this->useTenantDb($dbName);

        $salon = Salon::create([
            'name' => $salonName,
            'status' => 'active',
        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $userId,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        Client::create([
            'salon_id' => $salon->id,
            'first_name' => $clientName,
            'last_name' => 'Test',
            'phone' => '000-'.substr(md5($clientName),0,6),
            'email' => strtolower(str_replace(' ','',$clientName)).'@example.com',
            'status' => 'active',
        ]);
    }

    private function firstSalonId(string $dbName): int
    {
        $this->useTenantDb($dbName);
        return (int) Salon::query()->orderBy('id')->value('id');
    }
}
