<?php

namespace Tests\Feature\Module4;

use App\Models\NotificationOutbox;
use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OutboxEnqueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enqueue(): void
    {
        $user = User::factory()->create();
        $salon = Salon::factory()->create();
        SalonMember::create(['salon_id'=>$salon->id,'user_id'=>$user->id,'role'=>'owner','status'=>'active','joined_at'=>now()]);
        $user->update(['active_salon_id'=>$salon->id]);

        Sanctum::actingAs($user);

        $resp = $this->withHeader('X-Salon-Id',$salon->id)
            ->postJson('/api/notifications/outbox', [
                'channel'=>'email',
                'template'=>'invite_created',
                'payload'=>['to_email'=>$user->email,'salon_name'=>$salon->name,'invite_link'=>'x'],
            ]);

        $resp->assertStatus(201);
        $this->assertDatabaseCount('notification_outbox', 1);
    }
}
