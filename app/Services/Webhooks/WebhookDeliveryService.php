<?php

namespace App\Services\Webhooks;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Support\Facades\Http;

class WebhookDeliveryService
{
    public function __construct(
        private WebhookSigner $signer,
        private WebhookBackoff $backoff
    ) {}

    public function process(int $limit = 50): int
    {
        $rows = WebhookDelivery::query()
            ->where('status','pending')
            ->where(function($q){
                $q->whereNull('next_attempt_at')->orWhere('next_attempt_at','<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $sent = 0;
        foreach ($rows as $d) {
            $ok = $this->deliver($d);
            if ($ok) $sent++;
        }

        return $sent;
    }

    public function deliver(WebhookDelivery $d): bool
    {
        $sub = WebhookSubscription::find($d->subscription_id);
        if (!$sub || !$sub->enabled) {
            $d->update([
                'status' => 'failed',
                'last_error' => 'Subscription disabled or missing',
            ]);
            return false;
        }

        $attempts = (int)$d->attempts + 1;
        $max = $this->backoff->maxAttempts();

        $body = json_encode([
            'id' => $d->delivery_id,
            'event' => $d->event,
            'payload' => $d->payload,
        ], JSON_UNESCAPED_SLASHES);

        $ts = (string) now()->timestamp;
        $sig = $this->signer->signature($sub->secret, $d->delivery_id, $ts, $body);

        try {
            $resp = Http::timeout((int)env('WEBHOOK_HTTP_TIMEOUT', 8))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Krema-Webhook-Id' => $d->delivery_id,
                    'X-Krema-Webhook-Event' => $d->event,
                    'X-Krema-Webhook-Timestamp' => $ts,
                    'X-Krema-Webhook-Signature' => $sig,
                    'X-Krema-Webhook-Signature-Version' => 'v1',
                ])
                ->post($sub->target_url, $body);

            $status = $resp->status();

            if ($status >= 200 && $status < 300) {
                $d->update([
                    'status' => 'sent',
                    'attempts' => $attempts,
                    'sent_at' => now(),
                    'last_http_status' => $status,
                    'last_error' => null,
                ]);
                return true;
            }

            return $this->failOrRetry($d, $attempts, "HTTP {$status}", $status, $max);

        } catch (\Throwable $e) {
            return $this->failOrRetry($d, $attempts, substr($e->getMessage(), 0, 1000), null, $max);
        }
    }

    private function failOrRetry(WebhookDelivery $d, int $attempts, string $err, ?int $httpStatus, int $max): bool
    {
        if ($attempts >= $max) {
            $d->update([
                'status' => 'failed',
                'attempts' => $attempts,
                'last_error' => $err,
                'last_http_status' => $httpStatus,
            ]);
            return false;
        }

        $delay = $this->backoff->nextDelaySeconds($attempts);
        $d->update([
            'status' => 'pending',
            'attempts' => $attempts,
            'next_attempt_at' => now()->addSeconds($delay),
            'last_error' => $err,
            'last_http_status' => $httpStatus,
        ]);

        return false;
    }
}
