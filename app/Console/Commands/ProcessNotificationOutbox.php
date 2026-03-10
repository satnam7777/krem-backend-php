<?php

namespace App\Console\Commands;

use App\Models\NotificationOutbox;
use App\Services\Notifications\Backoff;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessNotificationOutbox extends Command
{
    protected $signature = 'notifications:process-outbox {--limit=50}';
    protected $description = 'Process notification_outbox pending rows';

    public function handle(NotificationDispatcher $dispatcher, Backoff $backoff): int
    {
        $limit = (int)$this->option('limit');

        $rows = NotificationOutbox::query()
            ->where('status','pending')
            ->where(function ($q) {
                $q->whereNull('send_after')->orWhere('send_after','<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $sent = 0; $failed = 0;

        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                // lock row
                $locked = NotificationOutbox::where('id',$row->id)->lockForUpdate()->first();
                if (!$locked || $locked->status !== 'pending') { DB::rollBack(); continue; }

                $locked->attempts = (int)$locked->attempts + 1;

                $dispatcher->dispatch($locked);

                $locked->status = 'sent';
                $locked->sent_at = now();
                $locked->last_error = null;
                $locked->save();

                DB::commit();
                $sent++;
            } catch (\Throwable $e) {
                DB::rollBack();

                // update as pending with backoff or failed if too many attempts
                $attempts = ((int)$row->attempts) + 1;
                $maxAttempts = 8;

                $update = [
                    'attempts' => $attempts,
                    'last_error' => substr($e->getMessage(), 0, 2000),
                    'updated_at' => now(),
                ];

                if ($attempts >= $maxAttempts) {
                    $update['status'] = 'failed';
                } else {
                    $update['status'] = 'pending';
                    $update['send_after'] = now()->addSeconds($backoff->nextDelaySeconds($attempts));
                }

                NotificationOutbox::where('id',$row->id)->update($update);
                $failed++;
            }
        }

        $this->info("Outbox processed. sent={$sent} failed={$failed}");
        return 0;
    }
}
