<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()?->is_superadmin) {
            abort(403, 'SuperAdmin only');
        }
        return $next($request);
    }
}
