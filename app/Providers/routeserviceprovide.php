<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $userId = $request->user()?->id;
            $tenant = $request->attributes->get('currentTenant');
            $tenantId = $tenant?->id;

            $key = $tenantId
                ? ('t:'.$tenantId.':'.($userId ? 'u:'.$userId : 'ip:'.$request->ip()))
                : ($userId ? 'u:'.$userId : 'ip:'.$request->ip());

            $perMinute = (int) config('krema_hardening.rate_limits.api_per_minute', 120);

            return Limit::perMinute($perMinute)->by($key);
        });
    }
}
