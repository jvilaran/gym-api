<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Support\Collection;

class SummaryService
{
    /**
     * Build a weight/volume progress summary for the authenticated user.
     */
    public function forUser(User $user)
    {
        $exercises = $user->exercises()
            ->with('exerciseType')
            ->orderBy('created_at')
            ->get();

        if ($exercises->isEmpty()) {
            return response()->json([
                'message' => 'No hay datos suficientes para el resumen',
                'status' => false,
            ], 404);
        }

        $byType = $exercises->groupBy('exercise_type_id')
            ->map(fn (Collection $group) => $this->summarizeType($group))
            ->values();

        $summary = [
            'total_exercises' => $exercises->count(),
            'last_workout' => optional($exercises->last()->created_at)->toDateString(),
            'total_volume' => round($exercises->sum(fn (Exercise $e) => $e->volume()), 2),
            'by_exercise_type' => $byType,
        ];

        return response()->json([
            'summary' => $summary,
            'status' => true,
        ], 200);
    }

    /**
     * @param  Collection<int, Exercise>  $group
     * @return array<string, mixed>
     */
    private function summarizeType(Collection $group): array
    {
        $first = $group->first();

        $points = $group->map(fn (Exercise $e) => [
            'date' => optional($e->created_at)->toDateString(),
            'weight' => (float) $e->weight,
            'volume' => round($e->volume(), 2),
        ])->values();

        return [
            'exercise_type_id' => $first->exercise_type_id,
            'exercise_type' => optional($first->exerciseType)->name,
            'sessions' => $group->count(),
            'max_weight' => round((float) $group->max('weight'), 2),
            'total_volume' => round($group->sum(fn (Exercise $e) => $e->volume()), 2),
            'progression' => $points,
            'trend' => $this->trend($group),
        ];
    }

    /**
     * Compare the latest half of the history against the earlier half.
     *
     * @param  Collection<int, Exercise>  $group
     * @return array<string, mixed>
     */
    private function trend(Collection $group): array
    {
        $count = $group->count();

        if ($count < 2) {
            return [
                'weight_change' => 0.0,
                'volume_change' => 0.0,
                'direction' => 'stable',
            ];
        }

        $half = (int) floor($count / 2);
        $previous = $group->take($half);
        $current = $group->slice($half);

        $prevWeight = (float) $previous->avg('weight');
        $currWeight = (float) $current->avg('weight');
        $prevVolume = (float) $previous->avg(fn (Exercise $e) => $e->volume());
        $currVolume = (float) $current->avg(fn (Exercise $e) => $e->volume());

        $weightChange = round($currWeight - $prevWeight, 2);
        $volumeChange = round($currVolume - $prevVolume, 2);

        $direction = match (true) {
            $weightChange > 0 || $volumeChange > 0 => 'up',
            $weightChange < 0 || $volumeChange < 0 => 'down',
            default => 'stable',
        };

        return [
            'weight_change' => $weightChange,
            'volume_change' => $volumeChange,
            'direction' => $direction,
        ];
    }
}
