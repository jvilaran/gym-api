<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_exercise_belongs_to_user_and_type(): void
    {
        $user = User::factory()->create();
        $type = ExerciseType::factory()->create();

        $exercise = Exercise::factory()
            ->forUser($user)
            ->ofType($type)
            ->create();

        $this->assertTrue($exercise->user->is($user));
        $this->assertTrue($exercise->exerciseType->is($type));
    }

    public function test_user_has_many_exercises(): void
    {
        $user = User::factory()->create();
        Exercise::factory()->count(3)->forUser($user)->create();

        $this->assertCount(3, $user->exercises);
    }

    public function test_volume_is_series_times_reps_times_weight(): void
    {
        $exercise = Exercise::factory()->create([
            'weight' => 50,
            'series' => 4,
            'reps' => 10,
        ]);

        $this->assertSame(2000.0, $exercise->volume());
    }
}
