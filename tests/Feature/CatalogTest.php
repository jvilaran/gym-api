<?php

namespace Tests\Feature;

use App\Models\ExerciseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function cdn(string $suffix): string
    {
        $base = rtrim((string) config('catalog.base_url'), '/');
        $version = (string) config('catalog.version');

        return "{$base}@{$version}/{$suffix}";
    }

    private function sampleExercise(): array
    {
        return [
            'id' => 'biceps/barbell-curl',
            'slug' => 'barbell-curl',
            'name' => 'Curl con barra',
            'muscle' => 'biceps',
            'bodyPart' => 'arms',
            'equipment' => 'barbell',
            'category' => 'strength',
            'secondaryMuscles' => ['forearms'],
            'instructions' => ['Paso 1', 'Paso 2'],
            'file' => 'biceps/barbell-curl.gif',
            'gifUrl' => $this->cdn('biceps/barbell-curl.gif'),
        ];
    }

    public function test_catalog_requires_authentication(): void
    {
        $this->getJson('/api/catalog/muscles')->assertUnauthorized();
    }

    public function test_lists_muscles(): void
    {
        Http::fake([
            $this->cdn('api/es/muscles.json') => Http::response([
                ['muscle' => 'biceps', 'count' => 151, 'endpoint' => 'muscles/biceps.json'],
            ], 200),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/catalog/muscles')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('muscles.0.muscle', 'biceps');
    }

    public function test_rejects_unsupported_language(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/catalog/muscles?lang=fr')
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_rejects_unknown_muscle_without_calling_cdn(): void
    {
        Http::fake();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/catalog/muscles/notamuscle')->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_lists_exercises_by_muscle(): void
    {
        Http::fake([
            $this->cdn('api/es/muscles/biceps.json') => Http::response([
                'muscle' => 'biceps',
                'count' => 1,
                'exercises' => [$this->sampleExercise()],
            ], 200),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/catalog/muscles/biceps')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('exercises.0.gifUrl', $this->cdn('biceps/barbell-curl.gif'));
    }

    public function test_returns_503_when_cdn_unreachable(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/catalog/muscles')
            ->assertStatus(503)
            ->assertJsonPath('status', false);
    }

    public function test_from_catalog_creates_type_once_and_reuses(): void
    {
        Http::fake([
            $this->cdn('api/es/exercises/biceps/barbell-curl.json') => Http::response($this->sampleExercise(), 200),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $first = $this->postJson('/api/exercise-types/from-catalog', [
            'catalog_id' => 'biceps/barbell-curl',
        ]);

        $first->assertCreated()
            ->assertJsonPath('created', true)
            ->assertJsonPath('exercise_type.name', 'Curl con barra')
            ->assertJsonPath('exercise_type.gif_url', $this->cdn('biceps/barbell-curl.gif'))
            ->assertJsonPath('exercise_type.muscle', 'biceps');

        $this->assertDatabaseCount('exercise_types', 1);

        // Picking the same exercise again reuses the existing type.
        $second = $this->postJson('/api/exercise-types/from-catalog', [
            'catalog_id' => 'biceps/barbell-curl',
        ]);

        $second->assertOk()->assertJsonPath('created', false);
        $this->assertDatabaseCount('exercise_types', 1);
    }

    public function test_from_catalog_returns_404_for_missing_item(): void
    {
        Http::fake([
            $this->cdn('api/es/exercises/biceps/does-not-exist.json') => Http::response('', 404),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/exercise-types/from-catalog', [
            'catalog_id' => 'biceps/does-not-exist',
        ])->assertNotFound()->assertJsonPath('status', false);
    }

    public function test_from_catalog_validates_catalog_id(): void
    {
        Http::fake();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/exercise-types/from-catalog', [
            'catalog_id' => 'invalid id with spaces',
        ])->assertStatus(422);
    }
}
