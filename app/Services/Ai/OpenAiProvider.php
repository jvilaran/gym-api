<?php

namespace App\Services\Ai;

use RuntimeException;

/**
 * Stub for the OpenAI driver. The HTTP integration is intentionally left
 * unimplemented so the default stays on the cost-free `fake` driver. To enable
 * it, implement complete() using the OpenAI chat completions API and set
 * AI_PROVIDER=openai plus OPENAI_API_KEY in the environment.
 */
class OpenAiProvider implements AiProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model = 'gpt-4o-mini',
    ) {}

    public function complete(string $prompt, ?string $system = null): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY no está configurada.');
        }

        // TODO: call the OpenAI chat completions endpoint with $system + $prompt.
        throw new RuntimeException('OpenAiProvider no está implementado todavía.');
    }

    public function name(): string
    {
        return 'openai';
    }
}
