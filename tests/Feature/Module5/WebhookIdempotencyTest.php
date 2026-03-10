<?php

namespace Tests\Feature\Module5;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_is_idempotent_by_event_id(): void
    {
        $payload = ['id'=>'evt_123','type'=>'payment.succeeded'];

        $this->withHeader('X-Provider','stripe')
            ->postJson('/api/payments/webhooks', $payload)
            ->assertStatus(200);

        $this->withHeader('X-Provider','stripe')
            ->postJson('/api/payments/webhooks', $payload)
            ->assertStatus(200);

        $this->assertDatabaseCount('payment_webhook_events', 1);
    }
}
