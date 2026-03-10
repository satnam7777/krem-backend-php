<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Tenancy\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create
        {name : Tenant display name}
        {--domain= : Primary host/domain (e.g. demo.krema.ba)}
        {--slug= : Optional slug override}
        {--db= : Optional database name override}
        {--migrate : Run migrations for tenant after creation}';

    protected $description = 'Create a new tenant (DB-per-tenant) and optionally run tenant migrations';

    public function handle(TenantDatabaseManager $mgr): int
    {
        $name = (string) $this->argument('name');
        $slug = (string) ($this->option('slug') ?: $mgr->makeSlug($name));
        $dbName = (string) ($this->option('db') ?: $mgr->makeDbName($slug));
        $domain = (string) ($this->option('domain') ?: '');

        DB::beginTransaction();
        try {
            $tenant = Tenant::create([
                'name' => $name,
                'slug' => $slug,
                'db_name' => $dbName,
                'status' => 'active',
                'created_by' => null,
            ]);

            if ($domain !== '') {
                TenantDomain::create([
                    'tenant_id' => $tenant->id,
                    'host' => strtolower($domain),
                    'is_primary' => true,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        // Create DB outside the central transaction (Postgres CREATE DATABASE cannot run inside txn)
        try {
            $mgr->createDatabase($dbName);
        } catch (\Throwable $e) {
            $this->error('DB create failed: ' . $e->getMessage());
            $this->warn('Rolling back central tenant records...');
            TenantDomain::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();
            return self::FAILURE;
        }

        $this->info("Tenant created: #{$tenant->id} {$tenant->name} ({$tenant->slug}) DB={$dbName}");
        if ($domain !== '') $this->info("Domain mapped: {$domain}");

        if ((bool) $this->option('migrate')) {
            $this->info('Running tenant migrations...');
            $mgr->migrateTenant($dbName, false);
            $this->info('Tenant migrations done.');
        } else {
            $this->warn('Migrations not run. Use --migrate or run: php artisan tenant:migrate --tenant='.$tenant->id);
        }

        return self::SUCCESS;
    }
}
