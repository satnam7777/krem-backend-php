<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantMembership;
use App\Models\User;
use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\Service;
use App\Models\Staff;
use App\Tenancy\TenantDatabaseManager;
use Illuminate\Console\Command;

class KremaBootstrapDemo extends Command
{
    protected $signature = 'krema:bootstrap-demo {--host=demo.local} {--tenant=Demo Salon Group} {--email=demo.owner@example.com} {--password=Password123!}';
    protected $description = 'Bootstraps a demo tenant with an owner, a first salon, and sample catalog';

    public function handle(): int
    {
        /** @var TenantDatabaseManager $mgr */
        $mgr = app(TenantDatabaseManager::class);

        $host = strtolower($this->option('host'));
        $tenantName = $this->option('tenant');
        $email = strtolower($this->option('email'));
        $password = $this->option('password');

        $super = User::firstOrCreate(
            ['email' => 'superadmin@krema.local'],
            ['name' => 'Super Admin', 'password' => bcrypt('Password123!'), 'is_superadmin' => true, 'is_active' => true]
        );

        $slug = $mgr->makeSlug($tenantName);
        $dbName = $mgr->makeDbName($slug);

        $tenant = Tenant::firstOrCreate(
            ['slug' => $slug],
            ['name' => $tenantName, 'db_name' => $dbName, 'status' => 'active', 'created_by' => $super->id]
        );

        TenantDomain::firstOrCreate(
            ['host' => $host],
            ['tenant_id' => $tenant->id, 'is_primary' => true]
        );

        $owner = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Demo Owner', 'password' => bcrypt($password), 'is_superadmin' => false, 'is_active' => true]
        );

        TenantMembership::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $owner->id],
            ['role' => 'OWNER', 'status' => 'active']
        );

        if (!$mgr->databaseExists($dbName)) {
            $mgr->createDatabase($dbName);
        }
        $mgr->migrateTenant($dbName, false);

        config(['database.connections.tenant.database' => $dbName]);
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\DB::reconnect('tenant');

        $salon = Salon::firstOrCreate(
            ['name' => 'Demo Salon'],
            ['status' => 'active', 'timezone' => 'Europe/Sarajevo']
        );

        SalonMember::firstOrCreate(
            ['salon_id' => $salon->id, 'user_id' => $owner->id],
            ['role' => 'OWNER', 'status' => 'active']
        );

        Staff::firstOrCreate(
            ['salon_id' => $salon->id, 'name' => 'Mia'],
            ['title' => 'Stylist', 'is_active' => true, 'sort_order' => 1]
        );

        Service::firstOrCreate(
            ['salon_id' => $salon->id, 'name' => 'Haircut'],
            ['description' => 'Classic haircut', 'duration_min' => 45, 'buffer_min' => 0, 'price_cents' => 2500, 'currency' => 'EUR', 'is_active' => true, 'sort_order' => 1]
        );

        $this->info('Demo bootstrapped');
        $this->line('Host: '.$host);
        $this->line('Tenant: '.$tenant->name.' (db: '.$dbName.')');
        $this->line('Owner: '.$owner->email.' / '.$password);
        $this->line('Salon id: '.$salon->id);

        return self::SUCCESS;
    }
}
