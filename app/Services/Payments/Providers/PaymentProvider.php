<?php

namespace App\Services\Payments\Providers;

use App\Models\Order;
use App\Models\PaymentIntent;

interface PaymentProvider
{
    public function providerName(): string;

    public function createIntent(Order $order): PaymentIntent;

    public function handleWebhook(array $payload): void;
}
