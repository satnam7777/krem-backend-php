<?php

namespace App\Http\Middleware;

use App\Models\TenantMembership;
use Closure;
use Illuminate\Http\Request;

class RequireTenantMembership
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = $request->attributes->get('currentTenant');
        $user = $request->user();

        if (!$tenant || !$user) {
            return $next($request);
        }

        $m = TenantMembership::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$m) {
            abort(403, 'Tenant access denied');
        }

        $request->attributes->set('tenantMembershipRole', $m->role);

        return $next($request);
    }
}
