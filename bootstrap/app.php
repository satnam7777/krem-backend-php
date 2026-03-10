<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API middleware stack
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
                \App\Http\Middleware\AddRequestId::class,
                \App\Http\Middleware\LogContext::class,
                \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->api(append: [
            'throttle:api',
        ]);

        // Alias route middleware (used by modules)
        $middleware->alias([
            'resolveTenant' => \App\Http\Middleware\ResolveTenant::class,
            'resolveSalon'  => \App\Http\Middleware\ResolveSalon::class,
            'requireRole'   => \App\Http\Middleware\RequireRole::class,
            'requireTenantMembership' => \App\Http\Middleware\RequireTenantMembership::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
