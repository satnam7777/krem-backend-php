<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $role = $request->attributes->get('currentRole');
        if (!$role || !in_array($role, $roles, true)) {
            abort(403, 'Forbidden');
        }
        return $next($request);
    }
}
