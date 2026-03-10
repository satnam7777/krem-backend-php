<?php

namespace App\Services\Payments\Providers;

use App\Models\Order;
use App\Models\PaymentIntent;

class StripeProviderStub implements PaymentProvider
{
    public function providerName(): string { return 'stripe'; }

    public function createIntent(Order $order): PaymentIntent
    {
        return PaymentIntent::create([
            'salon_id' => $order->salon_id,
            'order_id' => $order->id,
            'provider' => 'stripe',
            'provider_intent_id' => null,
            'amount_cents' => $order->total_cents,
            'currency' => $order->currency,
            'status' => 'requires_payment_method',
            'provider_payload' => null,
        ]);
    }

    public function handleWebhook(array $payload): void
    {
        // Implement when integrating Stripe SDK.
    }
}
