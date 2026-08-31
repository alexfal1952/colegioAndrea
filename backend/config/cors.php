<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))
    ))),

    // Permite localhost, 127.0.0.1, 0.0.0.0 y IPs privadas Docker (ej. 172.19.0.5:5173)
    'allowed_origins_patterns' => [
        '#^https?://(localhost|127\.0\.0\.1|0\.0\.0\.0)(:\d+)?$#',
        '#^https?://(10|172\.(1[6-9]|2\d|3[0-1])|192\.168)\.\d{1,3}\.\d{1,3}(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
