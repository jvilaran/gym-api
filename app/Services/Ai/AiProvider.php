<?php

namespace App\Services\Ai;

interface AiProvider
{
    /**
     * Generate a natural-language completion for the given prompt.
     *
     * Implementations should treat $prompt as the full instruction and return
     * plain text. The system prompt (coach persona) is optional context.
     */
    public function complete(string $prompt, ?string $system = null): string;

    /**
     * Identifier of the provider ("fake", "openai", ...). Useful for responses
     * and debugging so the frontend knows which driver produced the text.
     */
    public function name(): string;
}
