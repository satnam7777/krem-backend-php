<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

class AppointmentRescheduleConflictTest extends TenancyTestCase
{
    public function test_reschedule_conflict_returns_409_or_is_skipped_if_not_supported(): void
    {
        [, $db] = $this->provisionTenant('Reschedule', 'res.local');
        $this->useTenantDb($db);

        $salon = Salon::create(['name' => 'Res Salon', 'status' => 'active', 'timezone' => 'Europe/Sarajevo']);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@res.local',
            'password' => bcrypt('password'),        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $owner->id,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Massage',
            'duration_min' => 60,
            'buffer_min' => 0,
            'price_cents' => 5000,
            'currency' => 'EUR',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $staff = Staff::create([
            'salon_id' => $salon->id,
            'name' => 'Staff 1',
            'title' => 'Therapist',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($owner, ['*']);
        $headers = ['Host' => 'res.local', 'X-Salon-Id' => (string)$salon->id];

        $start1 = Carbon::parse('2026-02-26 10:00:00', 'Europe/Sarajevo');
        $payload1 = [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'start_at' => $start1->toDateTimeString(),
            'end_at' => $start1->copy()->addMinutes(60)->toDateTimeString(),
            'timezone' => 'Europe/Sarajevo',
            'customer_name' => 'Ana',
            'customer_phone' => '111-222',
        ];

        $r1 = $this->withHeaders($headers)->postJson('/api/appointments', $payload1);
        $r1->assertStatus(201);
        $id1 = $r1->json('id') ?? $r1->json('data.id');

        $start2 = Carbon::parse('2026-02-26 12:00:00', 'Europe/Sarajevo');
        $payload2 = $payload1;
        $payload2['start_at'] = $start2->toDateTimeString();
        $payload2['end_at'] = $start2->copy()->addMinutes(60)->toDateTimeString();
        $this->withHeaders($headers)->postJson('/api/appointments', $payload2)->assertStatus(201);

        // Try to reschedule first appointment into conflicting slot (12:00)
        $payloadUpdate = [
            'start_at' => $start2->toDateTimeString(),
            'end_at' => $start2->copy()->addMinutes(60)->toDateTimeString(),
            'timezone' => 'Europe/Sarajevo',
        ];

        $r2 = $this->withHeaders($headers)->patchJson('/api/appointments/'.$id1, $payloadUpdate);
        $r2->assertStatus(409);
    }
}
