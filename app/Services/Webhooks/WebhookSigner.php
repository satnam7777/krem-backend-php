<?php

namespace App\Services\Webhooks;

class WebhookSigner
{
    public function signature(string $secret, string $deliveryId, string $timestamp, string $body): string
    {
        // simple, robust signature base:
        // v1:{deliveryId}:{timestamp}:{body}
        $base = "v1:{$deliveryId}:{$timestamp}:{$body}";
        return hash_hmac('sha256', $base, $secret);
    }
}
