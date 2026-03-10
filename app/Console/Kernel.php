<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Tenancy commands
     */
    protected $commands = [
        \App\Console\Commands\TenantCreate::class,
        \App\Console\Commands\TenantMigrate::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Module 3: auto no-show command (if present)
        if (class_exists(\App\Console\Commands\MarkNoShows::class)) {
            $schedule->command('krema:appointments:mark-no-shows')->hourly()->withoutOverlapping();
        }

        // Module 4: notification outbox processor
        if (class_exists(\App\Console\Commands\ProcessNotificationOutbox::class)) {
            $schedule->command('krema:notifications:process-outbox --limit=200')->everyMinute()->withoutOverlapping();
        }

        // Module 9: outgoing webhooks processor
        if (class_exists(\App\Console\Commands\ProcessWebhookDeliveries::class)) {
            $schedule->command('krema:webhooks:process --limit=100')->everyMinute()->withoutOverlapping();
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
