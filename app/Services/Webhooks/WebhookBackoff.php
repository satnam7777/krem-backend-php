<?php

namespace App\Services\Webhooks;

class WebhookBackoff
{
    public function nextDelaySeconds(int $attempts): int
    {
        // exponential-ish: 1m, 2m, 5m, 10m, 30m, 60m
        return match (true) {
            $attempts <= 1 => 60,
            $attempts == 2 => 120,
            $attempts == 3 => 300,
            $attempts == 4 => 600,
            $attempts == 5 => 1800,
            default => 3600,
        };
    }

    public function maxAttempts(): int
    {
        return (int) env('WEBHOOK_MAX_ATTEMPTS', 8);
    }
}
