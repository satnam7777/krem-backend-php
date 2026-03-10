<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogContext
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->tenantContext->tenantId();
        $userId = $request->user()?->id;
        $rid = $request->headers->get('X-Request-Id');

        Log::withContext([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'request_id' => $rid,
        ]);

        return $next($request);
    }
}
