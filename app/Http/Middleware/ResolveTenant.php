<?php

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        private TenantContext $context,
        private TenantDatabaseManager $db
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        $domain = TenantDomain::with('tenant')
            ->where('host', $host)
            ->first();

        if (!$domain || !$domain->tenant) {
            // If you want a public landing page, allow it here.
            return response()->json([
                'error' => 'TENANT_NOT_FOUND',
                'message' => 'Unknown tenant for host: ' . $host,
            ], 404);
        }

        if (($domain->tenant->status ?? 'active') !== 'active') {
            return response()->json([
                'error' => 'TENANT_SUSPENDED',
                'message' => 'Tenant is suspended.',
            ], 403);
        }

        $this->context->setTenant($domain->tenant);
        $this->db->switchToTenantDb($domain->tenant->db_name);
        
        // expose for controllers (optional)
        $request->attributes->set('currentTenant', $domain->tenant);

        return $next($request);
    }
}
