<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AddRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $rid = $request->headers->get('X-Request-Id') ?: (string) Str::uuid();
        $request->headers->set('X-Request-Id', $rid);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-Id', $rid);

        return $response;
    }
}
