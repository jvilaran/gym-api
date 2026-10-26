<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $types = collect([
            'Press de banca',
            'Sentadilla',
            'Peso muerto',
        ])->map(fn (string $name) => ExerciseType::factory()->create(['name' => $name]));

        // Historial con progresión lineal a lo largo de 8 semanas por tipo de ejercicio.
        foreach ($types as $type) {
            $baseWeight = fake()->numberBetween(40, 80);

            for ($week = 7; $week >= 0; $week--) {
                $date = now()->subWeeks($week);

                Exercise::factory()
                    ->forUser($user)
                    ->ofType($type)
                    ->create([
                        'weight' => $baseWeight + (7 - $week) * 2.5,
                        'series' => 4,
                        'reps' => fake()->numberBetween(8, 10),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
            }
        }
    }
}
