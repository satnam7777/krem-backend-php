<?php

namespace Tests\Feature\Module3;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvailabilitySmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_returns_slots(): void
    {
        $user = User::factory()->create();
        $salon = Salon::factory()->create(['timezone'=>'Europe/Sarajevo']);
        SalonMember::create(['salon_id'=>$salon->id,'user_id'=>$user->id,'role'=>'owner','status'=>'active','joined_at'=>now()]);
        $user->update(['active_salon_id'=>$salon->id]);

        $staff = Staff::create(['salon_id'=>$salon->id,'name'=>'A','is_active'=>true]);
        $service = Service::create(['salon_id'=>$salon->id,'name'=>'S','price_cents'=>1000,'duration_min'=>30,'buffer_min'=>0,'is_active'=>true]);

        // Weekday schedule window
        $date = now('Europe/Sarajevo')->toDateString();
        $weekday = now('Europe/Sarajevo')->dayOfWeek;

        StaffSchedule::create([
            'salon_id'=>$salon->id,'staff_id'=>$staff->id,'weekday'=>$weekday,'date'=>null,
            'start_time'=>'10:00','end_time'=>'11:00','is_available'=>true
        ]);

        Sanctum::actingAs($user);

        $resp = $this->withHeader('X-Salon-Id',$salon->id)
            ->getJson("/api/availability?date={$date}&service_id={$service->id}&staff_id={$staff->id}&slot_minutes=30");

        $resp->assertStatus(200);
        $this->assertTrue(count($resp->json('data')) >= 1);
    }
}
