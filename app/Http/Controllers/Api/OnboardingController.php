<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantMembership;
use App\Models\User;
use App\Models\Salon;
use App\Models\SalonMember;
use App\Tenancy\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function bootstrap(Request $request)
    {
        $actor = $request->user();
        if (!$actor || !$actor->is_superadmin) {
            abort(403, 'Superadmin only');
        }

        $data = $request->validate([
            'tenant_name' => ['required','string','min:2','max:120'],
            'tenant_host' => ['required','string','min:3','max:190'],
            'owner_name' => ['required','string','min:2','max:120'],
            'owner_email' => ['required','email','max:190'],
            'owner_phone' => ['nullable','string','max:50'],
            'owner_password' => ['nullable','string','min:8','max:190'],
            'first_salon_name' => ['required','string','min:2','max:120'],
            'timezone' => ['nullable','string','max:80'],
        ]);

        /** @var TenantDatabaseManager $mgr */
        $mgr = app(TenantDatabaseManager::class);

        $slug = $mgr->makeSlug($data['tenant_name']);
        $dbName = $mgr->makeDbName($slug);

        $tenant = Tenant::create([
            'name' => $data['tenant_name'],
            'slug' => $slug,
            'db_name' => $dbName,
            'status' => 'active',
            'created_by' => $actor->id,
        ]);

        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'host' => strtolower($data['tenant_host']),
            'is_primary' => true,
        ]);

        $email = strtolower($data['owner_email']);
        $owner = User::where('email', $email)->first();
        if (!$owner) {
            $pwd = $data['owner_password'] ?? Str::random(14);
            $owner = User::create([
                'name' => $data['owner_name'],
                'email' => $email,
                'phone' => $data['owner_phone'] ?? null,
                'password' => bcrypt($pwd),
                'is_superadmin' => false,
                'is_active' => true,
            ]);
        }

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

        $salon = Salon::create([
            'name' => $data['first_salon_name'],
            'status' => 'active',
            'timezone' => $data['timezone'] ?? 'Europe/Sarajevo',
        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $owner->id,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        return response()->json(['data' => [
            'tenant' => ['id' => $tenant->id, 'slug' => $tenant->slug, 'host' => strtolower($data['tenant_host']), 'db' => $dbName],
            'owner' => ['id' => $owner->id, 'email' => $owner->email],
            'salon' => ['id' => $salon->id, 'name' => $salon->name],
        ]], 201);
    }
}
