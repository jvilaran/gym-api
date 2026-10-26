<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExerciseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/exercises')->assertUnauthorized();
    }

    public function test_user_only_sees_their_own_exercises(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $type = ExerciseType::factory()->create();

        Exercise::factory()->count(2)->forUser($user)->ofType($type)->create();
        Exercise::factory()->count(3)->forUser($other)->ofType($type)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/exercises');

        $response->assertOk()->assertJsonPath('status', true);
        $this->assertCount(2, $response->json('exercises'));
    }

    public function test_store_assigns_exercise_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $type = ExerciseType::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/exercises', [
            'exercise_type_id' => $type->id,
            'weight' => 60,
            'series' => 4,
            'reps' => 8,
        ]);

        $response->assertCreated()->assertJsonPath('status', true);
        $this->assertDatabaseHas('exercises', [
            'user_id' => $user->id,
            'exercise_type_id' => $type->id,
            'weight' => 60,
        ]);
    }

    public function test_store_rejects_user_id_from_request(): void
    {
        $user = User::factory()->create();
        $type = ExerciseType::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/exercises', [
            'user_id' => 99999,
            'exercise_type_id' => $type->id,
            'weight' => 60,
            'series' => 4,
            'reps' => 8,
        ])->assertCreated();

        // user_id from the request body must be ignored; owner is the token user.
        $this->assertDatabaseMissing('exercises', ['user_id' => 99999]);
        $this->assertDatabaseHas('exercises', ['user_id' => $user->id]);
    }

    public function test_show_update_destroy_scoped_to_owner(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $type = ExerciseType::factory()->create();

        $own = Exercise::factory()->forUser($user)->ofType($type)->create();
        $foreign = Exercise::factory()->forUser($other)->ofType($type)->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/exercises/{$own->id}")->assertOk();
        $this->getJson("/api/exercises/{$foreign->id}")->assertNotFound();

        $this->putJson("/api/exercises/{$own->id}", ['weight' => 80])
            ->assertOk()
            ->assertJsonPath('status', true);
        $this->assertDatabaseHas('exercises', ['id' => $own->id, 'weight' => 80]);

        $this->putJson("/api/exercises/{$foreign->id}", ['weight' => 80])
            ->assertNotFound();

        $this->deleteJson("/api/exercises/{$foreign->id}")->assertNotFound();
        $this->deleteJson("/api/exercises/{$own->id}")->assertOk();
        $this->assertDatabaseMissing('exercises', ['id' => $own->id]);
    }

    public function test_show_returns_404_for_missing_exercise(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/exercises/999999')->assertNotFound();
    }
}
