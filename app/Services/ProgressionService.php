<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Deterministic progression rules. No AI here: given a user's history per
 * exercise type, it computes the next recommended weight / series / reps using
 * a simple linear/double-progression scheme. The LLM only narrates the result.
 */
class ProgressionService
{
    private const REP_TARGET = 10;      // top of the working rep range
    private const WEIGHT_STEP = 2.5;    // kg added when the rep target is reached
    private const REP_STEP = 1;         // reps added otherwise

    /**
     * Build a recommendation per exercise type for the given user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUser(User $user): array
    {
        return $user->exercises()
            ->with('exerciseType')
            ->orderBy('created_at')
            ->get()
            ->groupBy('exercise_type_id')
            ->map(fn (Collection $group) => $this->recommendForType($group))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Exercise>  $group
     * @return array<string, mixed>
     */
    private function recommendForType(Collection $group): array
    {
        /** @var Exercise $last */
        $last = $group->last();

        $current = [
            'weight' => (float) $last->weight,
            'series' => (int) $last->series,
            'reps' => (int) $last->reps,
        ];

        // Double progression: push reps up to REP_TARGET, then add weight and
        // reset reps to the bottom of the range.
        if ($current['reps'] >= self::REP_TARGET) {
            $next = [
                'weight' => round($current['weight'] + self::WEIGHT_STEP, 2),
                'series' => $current['series'],
                'reps' => max(self::REP_TARGET - 2, 6),
            ];
            $rationale = 'Alcanzaste el tope de repeticiones, sube el peso y baja las reps.';
        } else {
            $next = [
                'weight' => $current['weight'],
                'series' => $current['series'],
                'reps' => $current['reps'] + self::REP_STEP,
            ];
            $rationale = 'Mantén el peso y suma una repetición por serie.';
        }

        return [
            'exercise_type_id' => $last->exercise_type_id,
            'exercise_type' => optional($last->exerciseType)->name,
            'current' => $current,
            'recommended' => $next,
            'rationale' => $rationale,
        ];
    }
}
