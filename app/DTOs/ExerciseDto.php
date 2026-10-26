<?php

namespace App\DTOs;

use App\Http\Requests\Api\ExerciseRequest;

readonly class ExerciseDto
{
    public function __construct(
        public int $userId,
        public int $exerciseTypeId,
        public float $weight,
        public int $series,
        public int $reps
    ) {}

    public static function fromRequest(ExerciseRequest $request, int $userId): self
    {
        $validated = $request->validated();

        return new self(
            userId: $userId,
            exerciseTypeId: $validated['exercise_type_id'],
            weight: $validated['weight'],
            series: $validated['series'],
            reps: $validated['reps']
        );
    }
}
