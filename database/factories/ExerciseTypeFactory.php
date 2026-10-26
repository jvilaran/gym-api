<?php

namespace Database\Factories;

use App\Models\ExerciseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExerciseType>
 */
class ExerciseTypeFactory extends Factory
{
    protected $model = ExerciseType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Press de banca',
                'Sentadilla',
                'Peso muerto',
                'Press militar',
                'Dominadas',
                'Remo con barra',
                'Curl de bíceps',
                'Extensión de tríceps',
            ]),
            'status' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
