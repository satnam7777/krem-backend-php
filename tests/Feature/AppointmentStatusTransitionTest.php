<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

class AppointmentStatusTransitionTest extends TenancyTestCase
{
    public function test_status_transitions_or_skip_if_not_supported(): void
    {
        [, $db] = $this->provisionTenant('Statuses', 'st.local');
        $this->useTenantDb($db);

        $salon = Salon::create(['name' => 'Status Salon', 'status' => 'active', 'timezone' => 'Europe/Sarajevo']);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@st.local',
            'password' => bcrypt('password'),        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $owner->id,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Nails',
            'duration_min' => 30,
            'buffer_min' => 0,
            'price_cents' => 2000,
            'currency' => 'EUR',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $staff = Staff::create([
            'salon_id' => $salon->id,
            'name' => 'Staff 1',
            'title' => 'Tech',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($owner, ['*']);
        $headers = ['Host' => 'st.local', 'X-Salon-Id' => (string)$salon->id];

        $start = Carbon::parse('2026-02-26 09:00:00', 'Europe/Sarajevo');
        $payload = [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'start_at' => $start->toDateTimeString(),
            'end_at' => $start->copy()->addMinutes(30)->toDateTimeString(),
            'timezone' => 'Europe/Sarajevo',
            'customer_name' => 'Iva',
            'customer_phone' => '333-444',
        ];

        $r = $this->withHeaders($headers)->postJson('/api/appointments', $payload);
        $r->assertStatus(201);
        $id = $r->json('id') ?? $r->json('data.id');

        // Try status endpoint: PATCH /api/appointments/{id}/status
        $r2 = $this->withHeaders($headers)->patchJson('/api/appointments/'.$id.'/status', ['status' => 'confirmed']);
        $r2->assertOk();

        $r3 = $this->withHeaders($headers)->patchJson('/api/appointments/'.$id.'/status', ['status' => 'completed']);
        $r3->assertOk();

        $r4 = $this->withHeaders($headers)->patchJson('/api/appointments/'.$id.'/status', ['status' => 'cancelled']);
        // completed -> cancelled should be blocked (expect 409/422). If your rules differ, adjust.
        $this->assertTrue(in_array($r4->status(), [409, 422]));
    }
}
