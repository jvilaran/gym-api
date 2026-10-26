<?php

namespace App\Services;

use App\Models\User;
use App\Services\Ai\AiProvider;

/**
 * Orchestrates AI features. The numbers always come from deterministic
 * services (ProgressionService / SummaryService); the AiProvider is only used
 * to turn those facts into a natural-language, coach-style message.
 */
class AiService
{
    private const SYSTEM = 'Eres un entrenador personal de gimnasio. Responde breve, '
        .'motivador y en español, basándote solo en los datos proporcionados.';

    public function __construct(
        private readonly AiProvider $ai,
        private readonly ProgressionService $progression,
        private readonly SummaryService $summary,
    ) {}

    public function recommendation(User $user)
    {
        $recommendations = $this->progression->forUser($user);

        if (empty($recommendations)) {
            return response()->json([
                'message' => 'No hay historial para generar una recomendación',
                'status' => false,
            ], 404);
        }

        $prompt = $this->buildRecommendationPrompt($recommendations);
        $message = $this->ai->complete($prompt, self::SYSTEM);

        return response()->json([
            'recommendations' => $recommendations,
            'message' => $message,
            'provider' => $this->ai->name(),
            'status' => true,
        ], 200);
    }

    public function analysis(User $user)
    {
        $summary = $this->summary->forUser($user);

        // Reuse the SummaryService response; if it was a 404, propagate it.
        $payload = $summary->getData(true);

        if (($payload['status'] ?? false) !== true) {
            return $summary;
        }

        $prompt = $this->buildAnalysisPrompt($payload['summary']);
        $message = $this->ai->complete($prompt, self::SYSTEM);

        return response()->json([
            'summary' => $payload['summary'],
            'message' => $message,
            'provider' => $this->ai->name(),
            'status' => true,
        ], 200);
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    private function buildRecommendationPrompt(array $recommendations): string
    {
        $lines = ['Recomendaciones de progresión calculadas:'];

        foreach ($recommendations as $r) {
            $lines[] = sprintf(
                '- %s: ahora %skg x%d x%d reps -> sugerido %skg x%d x%d reps (%s)',
                $r['exercise_type'] ?? 'Ejercicio',
                $r['current']['weight'],
                $r['current']['series'],
                $r['current']['reps'],
                $r['recommended']['weight'],
                $r['recommended']['series'],
                $r['recommended']['reps'],
                $r['rationale'],
            );
        }

        $lines[] = 'Redacta un mensaje corto y motivador explicando la progresión.';

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    private function buildAnalysisPrompt(array $summary): string
    {
        $lines = [
            'Resumen de progreso del usuario:',
            sprintf('- Ejercicios registrados: %d', $summary['total_exercises'] ?? 0),
            sprintf('- Último entrenamiento: %s', $summary['last_workout'] ?? 'desconocido'),
            sprintf('- Volumen total: %s', $summary['total_volume'] ?? 0),
        ];

        foreach (($summary['by_exercise_type'] ?? []) as $type) {
            $lines[] = sprintf(
                '- %s: peso máx %s, volumen %s, tendencia %s',
                $type['exercise_type'] ?? 'Ejercicio',
                $type['max_weight'] ?? 0,
                $type['total_volume'] ?? 0,
                $type['trend']['direction'] ?? 'stable',
            );
        }

        $lines[] = 'Redacta un análisis breve en lenguaje natural sobre su progreso.';

        return implode("\n", $lines);
    }
}
