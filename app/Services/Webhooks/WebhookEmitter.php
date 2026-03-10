<?php

namespace App\Services\Webhooks;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Support\Str;

class WebhookEmitter
{
    public function emit(int $salonId, string $event, array $payload): int
    {
        $subs = WebhookSubscription::where('salon_id',$salonId)->where('enabled',true)->get();

        $count = 0;
        foreach ($subs as $sub) {
            if (!$this->subscribesTo($sub, $event)) continue;

            WebhookDelivery::create([
                'salon_id' => $salonId,
                'subscription_id' => $sub->id,
                'event' => $event,
                'delivery_id' => Str::uuid()->toString(),
                'payload' => $payload,
                'status' => 'pending',
                'attempts' => 0,
                'next_attempt_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    private function subscribesTo(WebhookSubscription $sub, string $event): bool
    {
        $events = $sub->events ?? null;
        if (!$events || count($events) === 0) return true;
        return in_array($event, $events, true);
    }
}
