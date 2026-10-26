<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| The SPA frontend runs on a different origin and talks to this API with
| Sanctum bearer tokens (no cookies), so we allow the API paths for the
| configured web origins. Set CORS_ALLOWED_ORIGINS to a comma-separated
| list of origins (e.g. "http://localhost:4200,https://app.example.com").
|
| https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
|
*/

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:4200'))
)));

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Token auth uses the Authorization header, not cookies.
    'supports_credentials' => false,

];
