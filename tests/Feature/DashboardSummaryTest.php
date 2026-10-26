<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/dashboard/summary')->assertUnauthorized();
    }

    public function test_returns_404_when_no_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/dashboard/summary')
            ->assertNotFound()
            ->assertJsonPath('status', false);
    }

    public function test_summary_is_scoped_and_computes_volume(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $type = ExerciseType::factory()->create(['name' => 'Press de banca']);

        // Two sessions: volume = 50*4*10 + 60*4*10 = 2000 + 2400 = 4400
        Exercise::factory()->forUser($user)->ofType($type)->create([
            'weight' => 50, 'series' => 4, 'reps' => 10,
            'created_at' => now()->subWeek(),
        ]);
        Exercise::factory()->forUser($user)->ofType($type)->create([
            'weight' => 60, 'series' => 4, 'reps' => 10,
            'created_at' => now(),
        ]);

        // Noise from another user must not leak in.
        Exercise::factory()->forUser($other)->ofType($type)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/summary');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('summary.total_exercises', 2)
            ->assertJsonPath('summary.total_volume', 4400)
            ->assertJsonPath('summary.by_exercise_type.0.exercise_type', 'Press de banca')
            ->assertJsonPath('summary.by_exercise_type.0.max_weight', 60)
            ->assertJsonPath('summary.by_exercise_type.0.trend.direction', 'up');
    }
}
