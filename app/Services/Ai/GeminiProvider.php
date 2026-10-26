<?php

namespace App\Services\Ai;

use RuntimeException;

/**
 * Stub for the Google Gemini driver. Left unimplemented by design; enable by
 * implementing complete() against the Gemini generateContent API and setting
 * AI_PROVIDER=gemini plus GEMINI_API_KEY.
 */
class GeminiProvider implements AiProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model = 'gemini-1.5-flash',
    ) {}

    public function complete(string $prompt, ?string $system = null): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('GEMINI_API_KEY no está configurada.');
        }

        // TODO: call the Gemini generateContent endpoint with $system + $prompt.
        throw new RuntimeException('GeminiProvider no está implementado todavía.');
    }

    public function name(): string
    {
        return 'gemini';
    }
}
