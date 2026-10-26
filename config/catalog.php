<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exercise Catalog (ExerciseGymGifsDB)
    |--------------------------------------------------------------------------
    |
    | Static JSON API served over the jsDelivr CDN. No API key required. The
    | data is version-pinned, so it is safe to cache for a long time.
    | Source: https://github.com/JahelCuadrado/ExerciseGymGifsDB
    |
    */

    'base_url' => env('CATALOG_BASE_URL', 'https://cdn.jsdelivr.net/gh/JahelCuadrado/ExerciseGymGifsDB'),

    'version' => env('CATALOG_VERSION', 'v1.1.0'),

    'default_lang' => env('CATALOG_DEFAULT_LANG', 'es'),

    'supported_langs' => ['es', 'en'],

    // Cache TTL in seconds. Default 7 days (data is static + version pinned).
    'cache_ttl' => (int) env('CATALOG_CACHE_TTL', 604800),

    // HTTP timeout in seconds when calling the CDN.
    'timeout' => (int) env('CATALOG_TIMEOUT', 10),

    // Known muscle groups (used to validate the {muscle} route segment).
    'muscles' => [
        'abductors', 'abs', 'adductors', 'biceps', 'calves', 'cardio', 'delts',
        'forearms', 'glutes', 'hamstrings', 'lats', 'levator-scapulae',
        'pectorals', 'quads', 'serratus-anterior', 'spine', 'traps', 'triceps',
        'upper-back',
    ],

];
