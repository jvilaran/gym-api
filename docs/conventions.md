# Conventions

Follow these when adding or changing code so the codebase stays consistent.

## Request flow

```
Controller (thin) -> FormRequest -> DTO (readonly) -> Service -> Model
```

- Controllers only build the DTO and delegate to a service. No logic.
- FormRequests own validation. On failure, return JSON `422`:

  ```php
  throw new HttpResponseException(response()->json([
      'message' => 'Error de validacion',
      'errors' => $validator->errors(),
  ], 422));
  ```

- DTOs are `readonly` classes with a static `fromRequest()` that reads
  `$request->validated()`.
- Services contain the logic and return `response()->json($data, $status)`.

## Response shape

Always include a `status` boolean:

```php
return response()->json(['exercises' => $items, 'status' => true], 200);
return response()->json(['message' => 'No encontrado', 'status' => false], 404);
```

Status codes: `200` ok, `201` created, `401` unauthenticated, `404` not found,
`422` validation, `500` server error.

## Language

- API messages and seed data: **Spanish**.
- Code, comments, variable names, and documentation: **English**.

## Security

- Resource routes are behind `auth:sanctum`.
- Never trust `user_id` from the request body. Use `$request->user()` and scope
  queries through the user relation (e.g. `$user->exercises()`), which also
  enforces ownership on show/update/delete.

## Models

- Define relations and `casts()` on the model.
- `Exercise.weight` is cast to `decimal:2`.

## Tests

- Unit tests for pure logic (e.g. `ProgressionService`, model relations).
- Feature tests for endpoints (auth, scoping, status codes).
- Use factories (`UserFactory`, `ExerciseTypeFactory`, `ExerciseFactory`) and
  `Sanctum::actingAs()` for authenticated requests.
- Keep AI tests on the `fake` driver (`config()->set('ai.default', 'fake')`).
- JSON numbers after `round()` may serialize as integers; assert accordingly.
