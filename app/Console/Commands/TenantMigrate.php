<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Tenancy\TenantDatabaseManager;
use Illuminate\Console\Command;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate
        {--tenant= : Tenant ID or slug. If omitted, migrates ALL tenants}
        {--fresh : Run migrate:fresh on tenant DBs (DANGEROUS)}
        {--only-active : Only migrate active tenants (default true)}';

    protected $description = 'Run migrations against tenant databases';

    public function handle(TenantDatabaseManager $mgr): int
    {
        $onlyActive = $this->option('only-active') !== false;
        $fresh = (bool) $this->option('fresh');
        $selector = $this->option('tenant');

        $q = Tenant::query();
        if ($onlyActive) $q->where('status','active');

        if ($selector) {
            $tenant = is_numeric($selector)
                ? $q->where('id', (int)$selector)->first()
                : $q->where('slug', (string)$selector)->first();

            if (!$tenant) {
                $this->error('Tenant not found: ' . $selector);
                return self::FAILURE;
            }

            $this->line('Migrating tenant: '.$tenant->id.' '.$tenant->slug.' DB='.$tenant->db_name);
            $mgr->migrateTenant($tenant->db_name, $fresh);
            $this->info('OK');
            return self::SUCCESS;
        }

        $tenants = $q->orderBy('id')->get();
        if ($tenants->isEmpty()) {
            $this->warn('No tenants to migrate.');
            return self::SUCCESS;
        }

        foreach ($tenants as $t) {
            $this->line('Migrating tenant: '.$t->id.' '.$t->slug.' DB='.$t->db_name);
            try {
                $mgr->migrateTenant($t->db_name, $fresh);
                $this->info('OK');
            } catch (\Throwable $e) {
                $this->error('FAILED: '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
