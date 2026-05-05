<?php

namespace App\DTOs;

use App\Http\Requests\Api\ExerciseRequest;
use Illuminate\Http\Request;

readonly class ExerciseDto 
{
    public function __construct(
        public int $userId,
        public int $exerciseTypeId,
        public float $weight,
        public int $series,
        public int $reps
    ) {}

    public static function fromRequest(ExerciseRequest $request): self {
        $validated = $request->validated();
        
        return new self(
            userId: $validated['user_id'],
            exerciseTypeId: $validated['exercise_type_id'],
            weight: $validated['weight'],
            series: $validated['series'],
            reps: $validated['reps']
        );
    }
}