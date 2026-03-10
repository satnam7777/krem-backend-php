<?php

namespace Tests\Feature\Module2;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_is_salon_scoped(): void
    {
        $user = User::factory()->create();

        $salonA = Salon::factory()->create();
        $salonB = Salon::factory()->create();

        SalonMember::create(['salon_id'=>$salonA->id,'user_id'=>$user->id,'role'=>'owner','status'=>'active','joined_at'=>now()]);
        $user->update(['active_salon_id'=>$salonA->id]);

        $svcA = Service::create(['salon_id'=>$salonA->id,'name'=>'A','duration_min'=>30,'buffer_min'=>0,'price_cents'=>1000,'currency'=>'EUR','is_active'=>true,'sort_order'=>0]);
        $svcB = Service::create(['salon_id'=>$salonB->id,'name'=>'B','duration_min'=>30,'buffer_min'=>0,'price_cents'=>1000,'currency'=>'EUR','is_active'=>true,'sort_order'=>0]);

        Sanctum::actingAs($user);

        $this->withHeader('X-Salon-Id',$salonA->id)
            ->getJson('/api/catalog/services/'.$svcA->id)
            ->assertStatus(200);

        $this->withHeader('X-Salon-Id',$salonA->id)
            ->getJson('/api/catalog/services/'.$svcB->id)
            ->assertStatus(404);
    }
}
