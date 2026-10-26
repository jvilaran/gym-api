<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Driver
    |--------------------------------------------------------------------------
    |
    | Controls which AiProvider implementation is resolved from the container.
    | Defaults to the cost-free, deterministic "fake" driver so the app works
    | out of the box and tests never make network calls. Set AI_PROVIDER to
    | "openai", "anthropic" or "gemini" (and the matching API key) to switch.
    |
    */

    'default' => env('AI_PROVIDER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [

        'fake' => [
            // no configuration needed
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],

        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        ],

    ],

];
