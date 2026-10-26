<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('ai.default', 'fake');
    }

    public function test_recommendation_requires_authentication(): void
    {
        $this->getJson('/api/ai/recommendation')->assertUnauthorized();
    }

    public function test_analysis_requires_authentication(): void
    {
        $this->getJson('/api/ai/analysis')->assertUnauthorized();
    }

    public function test_recommendation_returns_404_without_history(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/ai/recommendation')
            ->assertNotFound()
            ->assertJsonPath('status', false);
    }

    public function test_recommendation_uses_fake_provider_and_is_scoped(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $type = ExerciseType::factory()->create(['name' => 'Sentadilla']);

        Exercise::factory()->forUser($user)->ofType($type)->create([
            'weight' => 50, 'series' => 4, 'reps' => 10, 'created_at' => now(),
        ]);
        Exercise::factory()->forUser($other)->ofType($type)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/ai/recommendation');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('provider', 'fake')
            ->assertJsonPath('recommendations.0.exercise_type', 'Sentadilla')
            ->assertJsonPath('recommendations.0.recommended.weight', 52.5);

        $this->assertCount(1, $response->json('recommendations'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_analysis_returns_natural_language_message(): void
    {
        $user = User::factory()->create();
        $type = ExerciseType::factory()->create();

        Exercise::factory()->count(3)->forUser($user)->ofType($type)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/ai/analysis')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('provider', 'fake')
            ->assertJsonStructure(['summary', 'message', 'provider', 'status']);
    }
}
