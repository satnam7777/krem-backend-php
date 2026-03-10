<?php

return [
    'rate_limits' => [
        'api_per_minute' => env('KREMA_API_RPM', 120),
    ],
];
