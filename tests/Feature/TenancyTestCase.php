<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantMembership;
use App\Tenancy\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

abstract class TenancyTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate', [
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);
    }

    protected function provisionTenant(string $name, string $host): array
    {
        /** @var TenantDatabaseManager $mgr */
        $mgr = app(TenantDatabaseManager::class);

        $slug = $mgr->makeSlug($name);
        $dbName = $mgr->makeDbName($slug);

        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'db_name' => $dbName,
            'status' => 'active',
            'created_by' => null,
        ]);

        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'host' => strtolower($host),
            'is_primary' => true,
        ]);

        $mgr->createDatabase($dbName);
        $mgr->switchToTenantDb($dbName);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        return [$tenant, $dbName];
    }

    protected function centralMembership(int $tenantId, int $userId, string $role = 'STAFF'): void
    {
        TenantMembership::updateOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId],
            ['role' => $role, 'status' => 'active']
        );
    }

    protected function useTenantDb(string $dbName): void
    {
        config(['database.connections.tenant.database' => $dbName]);
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\DB::reconnect('tenant');
    }
}
