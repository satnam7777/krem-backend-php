<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MedicalProfile;
use App\Models\PlatformAuditLog;
use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class MedicalAccessAndAuditTest extends TenancyTestCase
{
    public function test_reception_cannot_view_medical_but_owner_can_and_is_audited(): void
    {
        [$tenant, $db] = $this->provisionTenant('Clinic X', 'x.local');

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->centralMembership($tenant->id, $owner->id, 'OWNER');

        $reception = User::create([
            'name' => 'Reception',
            'email' => 'reception@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->centralMembership($tenant->id, $reception->id, 'RECEPTION');

        $this->useTenantDb($db);

        $salon = Salon::create(['name' => 'X Salon', 'status' => 'active']);

        SalonMember::insert([
            ['salon_id' => $salon->id, 'user_id' => $owner->id, 'role' => 'OWNER', 'status' => 'active'],
            ['salon_id' => $salon->id, 'user_id' => $reception->id, 'role' => 'RECEPTION', 'status' => 'active'],
        ]);

        $client = Client::create([
            'salon_id' => $salon->id,
            'first_name' => 'Ana',
            'last_name' => 'Test',
            'phone' => '111-222',
            'email' => 'ana@example.com',
            'status' => 'active',
        ]);

        $profile = MedicalProfile::create([
            'client_id' => $client->id,
            'allergies' => ['latex'],
            'notes' => 'Sensitive note',
        ]);

        Sanctum::actingAs($reception, ['*']);
        $this->withHeaders([
            'Host' => 'x.local',
            'X-Salon-Id' => (string)$salon->id,
        ])->getJson('/api/clients/'.$client->id.'/medical')->assertStatus(403);

        Sanctum::actingAs($owner, ['*']);
        $this->withHeaders([
            'Host' => 'x.local',
            'X-Salon-Id' => (string)$salon->id,
        ])->getJson('/api/clients/'.$client->id.'/medical')
          ->assertOk()
          ->assertJsonFragment(['client_id' => $client->id]);

        $audit = PlatformAuditLog::where('action','client_medical.view')->latest()->first();
        $this->assertNotNull($audit);

        $raw = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('medical_profiles')->where('id', $profile->id)->value('notes');
        $this->assertIsString($raw);
        $this->assertStringNotContainsString('Sensitive note', $raw);
    }
}
