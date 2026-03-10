<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

class AppointmentDoubleBookingTest extends TenancyTestCase
{
    public function test_cannot_double_book_same_staff_same_time(): void
    {
        [$tenant, $db] = $this->provisionTenant('Bookings', 'book.local');

        $this->centralMembership($tenant->id, $owner->id, 'OWNER');

        $this->useTenantDb($db);

        $salon = Salon::create(['name' => 'Book Salon', 'status' => 'active', 'timezone' => 'Europe/Sarajevo']);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@book.local',
            'password' => bcrypt('password'),
        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $owner->id,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        $service = Service::create([
            'salon_id' => $salon->id,
            'name' => 'Haircut',
            'description' => null,
            'duration_min' => 60,
            'buffer_min' => 0,
            'price_cents' => 3000,
            'currency' => 'EUR',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $staff = Staff::create([
            'salon_id' => $salon->id,
            'name' => 'Staff 1',
            'title' => 'Stylist',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($owner, ['*']);

        $start = Carbon::parse('2026-02-26 10:00:00', 'Europe/Sarajevo');
        $end = (clone $start)->addMinutes(60);

        $payload = [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'start_at' => $start->toDateTimeString(),
            'end_at' => $end->toDateTimeString(),
            'timezone' => 'Europe/Sarajevo',
            'customer_name' => 'Ana',
            'customer_phone' => '111-222',
        ];

        $this->withHeaders([
            'Host' => 'book.local',
            'X-Salon-Id' => (string)$salon->id,
        ])->postJson('/api/appointments', $payload)->assertStatus(201);

        $this->withHeaders([
            'Host' => 'book.local',
            'X-Salon-Id' => (string)$salon->id,
        ])->postJson('/api/appointments', $payload)->assertStatus(409);
    }
}
