<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\User;
use App\Services\ProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_adds_weight_when_rep_target_reached(): void
    {
        $user = User::factory()->create();
        $type = ExerciseType::factory()->create();

        Exercise::factory()->forUser($user)->ofType($type)->create([
            'weight' => 50, 'series' => 4, 'reps' => 10,
            'created_at' => now(),
        ]);

        $result = (new ProgressionService())->forUser($user);

        $this->assertCount(1, $result);
        $this->assertSame(52.5, $result[0]['recommended']['weight']);
        $this->assertSame(8, $result[0]['recommended']['reps']);
    }

    public function test_adds_reps_when_below_rep_target(): void
    {
        $user = User::factory()->create();
        $type = ExerciseType::factory()->create();

        Exercise::factory()->forUser($user)->ofType($type)->create([
            'weight' => 50, 'series' => 4, 'reps' => 7,
            'created_at' => now(),
        ]);

        $result = (new ProgressionService())->forUser($user);

        $this->assertSame(50.0, $result[0]['recommended']['weight']);
        $this->assertSame(8, $result[0]['recommended']['reps']);
    }

    public function test_uses_latest_session_per_type(): void
    {
        $user = User::factory()->create();
        $type = ExerciseType::factory()->create();

        Exercise::factory()->forUser($user)->ofType($type)->create([
            'weight' => 40, 'series' => 4, 'reps' => 8,
            'created_at' => now()->subWeek(),
        ]);
        Exercise::factory()->forUser($user)->ofType($type)->create([
            'weight' => 45, 'series' => 4, 'reps' => 10,
            'created_at' => now(),
        ]);

        $result = (new ProgressionService())->forUser($user);

        // Latest session hit rep target -> weight bump from 45.
        $this->assertSame(45.0, $result[0]['current']['weight']);
        $this->assertSame(47.5, $result[0]['recommended']['weight']);
    }
}
