<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentWebhookEvent;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function receive(Request $request)
    {
        $provider = $request->header('X-Provider', 'unknown');
        $payload = $request->all();

        $eventId = $payload['id'] ?? ($payload['event_id'] ?? null);
        if (!$eventId) {
            abort(422, 'Missing event id');
        }

        try {
            PaymentWebhookEvent::create([
                'provider' => $provider,
                'event_id' => (string)$eventId,
                'received_at' => now(),
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>true,'idempotent'=>true]);
        }

        return response()->json(['ok'=>true]);
    }
}
