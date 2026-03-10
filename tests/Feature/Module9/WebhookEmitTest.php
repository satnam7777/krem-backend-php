<?php

namespace Tests\Feature\Module9;

use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use App\Models\WebhookSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WebhookEmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_subscription_and_emit_test(): void
    {
        $user = User::factory()->create();
        $salon = Salon::factory()->create();

        SalonMember::create(['salon_id'=>$salon->id,'user_id'=>$user->id,'role'=>'owner','status'=>'active','joined_at'=>now()]);
        $user->update(['active_salon_id'=>$salon->id]);

        Sanctum::actingAs($user);

        $this->withHeader('X-Salon-Id',$salon->id)
            ->postJson('/api/integrations/webhooks', [
                'name'=>'Zapier',
                'target_url'=>'https://example.com/webhook',
                'enabled'=>true,
                'secret'=>str_repeat('a', 16),
                'events'=>['appointment.created'],
            ])->assertStatus(201);

        $this->withHeader('X-Salon-Id',$salon->id)
            ->postJson('/api/integrations/webhooks/test', [
                'event'=>'appointment.created',
                'payload'=>['hello'=>'world'],
            ])->assertStatus(200)
            ->assertJsonStructure(['enqueued']);
    }
}
