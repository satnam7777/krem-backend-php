<?php

namespace Tests\Feature\Module7;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingsUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upsert_setting(): void
    {
        $user = User::factory()->create();
        $salon = Salon::factory()->create();

        SalonMember::create(['salon_id'=>$salon->id,'user_id'=>$user->id,'role'=>'owner','status'=>'active','joined_at'=>now()]);
        $user->update(['active_salon_id'=>$salon->id]);

        Sanctum::actingAs($user);

        $this->withHeader('X-Salon-Id',$salon->id)
            ->postJson('/api/ops/settings', [
                'key'=>'timezone',
                'type'=>'string',
                'value'=>'Europe/Sarajevo',
            ])->assertStatus(201);

        $this->assertDatabaseHas('salon_settings', ['salon_id'=>$salon->id,'key'=>'timezone']);
        $this->assertDatabaseHas('audit_logs', ['action'=>'salon.setting.upserted']);
    }
}
