<?php

namespace App\Services\Platform;

use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;

class Audit
{
    public function log(Request $request, ?int $tenantId, string $action, array $meta = []): void
    {
        PlatformAuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent() ? substr($request->userAgent(), 0, 500) : null,
            'meta' => $meta ?: null,
        ]);
    }
}
