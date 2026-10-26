<?php

namespace App\DTOs;

use App\Http\Requests\Api\ExerciseUpdateRequest;

readonly class ExerciseUpdateDto
{
    public function __construct(
        public ?int $exerciseTypeId,
        public ?float $weight,
        public ?int $series,
        public ?int $reps
    ) {}

    public static function fromRequest(ExerciseUpdateRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            exerciseTypeId: $validated['exercise_type_id'] ?? null,
            weight: isset($validated['weight']) ? (float) $validated['weight'] : null,
            series: $validated['series'] ?? null,
            reps: $validated['reps'] ?? null
        );
    }
}
