<?php

return [

    'paths' => ['api/*', 'login', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173,http://localhost:3000,http://127.0.0.1:3000,http://localhost:5174,http://127.0.0.1:5174,http://localhost:4173,http://127.0.0.1:4173')),

    'allowed_origins_patterns' => [
        '#^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['*'],

    'max_age' => 0,

    'supports_credentials' => true,

];
