<?php

namespace App\Services\Notifications;

class Backoff
{
    public function nextDelaySeconds(int $attempts): int
    {
        // 1st retry 60s, then 5m, 15m, 60m, 6h, 24h...
        return match (true) {
            $attempts <= 0 => 0,
            $attempts === 1 => 60,
            $attempts === 2 => 300,
            $attempts === 3 => 900,
            $attempts === 4 => 3600,
            $attempts === 5 => 21600,
            default => 86400,
        };
    }
}
