<?php

namespace App\Services\Ai;

use RuntimeException;

/**
 * Stub for the Anthropic (Claude) driver. Left unimplemented by design; enable
 * by implementing complete() against the Anthropic Messages API and setting
 * AI_PROVIDER=anthropic plus ANTHROPIC_API_KEY.
 */
class AnthropicProvider implements AiProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model = 'claude-3-5-sonnet-latest',
    ) {}

    public function complete(string $prompt, ?string $system = null): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY no está configurada.');
        }

        // TODO: call the Anthropic Messages endpoint with $system + $prompt.
        throw new RuntimeException('AnthropicProvider no está implementado todavía.');
    }

    public function name(): string
    {
        return 'anthropic';
    }
}
