<?php

namespace App\Services\Ai;

/**
 * Deterministic provider used by default and in tests. It never performs
 * network calls: it synthesizes a readable, coach-style message from the
 * structured facts in the prompt so the system can be exercised without cost.
 *
 * Unlike a naive echo, it drops the trailing *instruction* lines of the prompt
 * (e.g. "Redacta un mensaje...") so those never leak into the output, and it
 * rewrites the fact lines into prose. Deterministic: same input -> same output.
 */
class FakeAiProvider implements AiProvider
{
    public function complete(string $prompt, ?string $system = null): string
    {
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", trim($prompt))),
            static fn (string $line) => $line !== '',
        ));

        // Separate the heading, the fact bullets, and drop any instruction
        // line (the part meant for the LLM, never for the user).
        $facts = [];
        foreach ($lines as $line) {
            if ($this->isInstruction($line)) {
                continue;
            }
            if (str_starts_with($line, '-')) {
                $facts[] = ltrim($line, "- \t");
            }
        }

        if ($facts === []) {
            return 'Resumen del entrenador: sigue registrando tus entrenamientos para '
                .'recibir recomendaciones personalizadas. ¡Buen trabajo!';
        }

        $body = implode(' ', array_map(
            static fn (string $fact) => rtrim($fact, '.').'.',
            $facts,
        ));

        return 'Resumen del entrenador: '.$body
            .' Mantén la constancia y la técnica; vas por buen camino. ¡Sigue así!';
    }

    /**
     * Instruction lines are directives aimed at the model, not facts to show.
     */
    private function isInstruction(string $line): bool
    {
        return (bool) preg_match('/^(redacta|escribe|genera|resume)\b/i', $line);
    }

    public function name(): string
    {
        return 'fake';
    }
}
