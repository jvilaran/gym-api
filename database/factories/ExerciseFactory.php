<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'exercise_type_id' => ExerciseType::factory(),
            'weight' => fake()->randomFloat(2, 10, 150),
            'series' => fake()->numberBetween(3, 5),
            'reps' => fake()->numberBetween(6, 12),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function ofType(ExerciseType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'exercise_type_id' => $type->id,
        ]);
    }
}
