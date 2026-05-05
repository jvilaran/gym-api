<?php

namespace App\DTOs;

use App\Http\Requests\Api\ExerciseTypeRequest;
use Illuminate\Http\Request;

readonly class ExerciseTypeDto 
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromRequest(ExerciseTypeRequest $request): self {
        $validated = $request->validated();
        
        return new self(
            name: $validated['name']
        );
    }
}
