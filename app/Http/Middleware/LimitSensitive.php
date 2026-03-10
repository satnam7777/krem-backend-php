<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LimitSensitive
{
    public function handle(Request $request, Closure $next, int $maxPerMinute = 30)
    {
        $key = 'sensitive:'.($request->user()?->id ?? 'guest').':'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxPerMinute)) {
            return response()->json(['message'=>'Too many requests'], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
