<?php

namespace App\Http\Controllers\Api\Notifications;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class HealthController
{
    public function __invoke(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        return response()->json([
            'ok' => true,
            'queue' => Config::get('queue.default'),
            'salon_id' => $salon?->id,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
