<?php

namespace App\Console\Commands;

use App\Services\Webhooks\WebhookDeliveryService;
use Illuminate\Console\Command;

class ProcessWebhookDeliveries extends Command
{
    protected $signature = 'krema:webhooks:process {--limit=50}';
    protected $description = 'Process pending outgoing webhook deliveries';

    public function handle(WebhookDeliveryService $svc): int
    {
        $n = $svc->process((int)$this->option('limit'));
        $this->info("Processed: {$n}");
        return self::SUCCESS;
    }
}
