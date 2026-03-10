<?php

namespace App\Services\Ops;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class Auditor
{
    public function log(Request $request, string $action, array $context = []): void
    {
        $salon = $request->attributes->get('currentSalon');
        $role = $request->attributes->get('currentRole');

        AuditLog::create([
            'salon_id' => $salon?->id,
            'actor_user_id' => $request->user()?->id,
            'actor_role' => $role,
            'action' => $action,
            'context' => $context,
            'ip' => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 512),
        ]);
    }
}
