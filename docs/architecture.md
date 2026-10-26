# Architecture

`gym-api` is a JSON REST API built with Laravel 12. It is consumed by a separate
frontend over Sanctum bearer tokens. There are no server-rendered views.

## Layers

Requests flow through a strict set of layers. This keeps controllers thin and
business logic testable.

```
HTTP request
   │
   ▼
Route (routes/api.php)
   │
   ▼
Controller            app/Http/Controllers  — thin; builds DTO, delegates
   │
   ▼
FormRequest           app/Http/Requests     — validation, JSON 422 on failure
   │
   ▼
DTO (readonly)        app/DTOs              — fromRequest(), typed data
   │
   ▼
Service               app/Services          — logic, returns response()->json()
   │
   ▼
Model (Eloquent)      app/Models            — relations, casts
   │
   ▼
PostgreSQL
```

## Domain model

```
User ──< Exercise >── ExerciseType

User          1 ── * Exercise
ExerciseType  1 ── * Exercise
Exercise      *  ── 1 User, *  ── 1 ExerciseType
```

- `Exercise` holds `weight` (decimal 8,2), `series`, `reps`, plus `user_id` and
  `exercise_type_id` foreign keys (`cascadeOnDelete`).
- `Exercise::volume()` returns `weight * series * reps`.
- `ExerciseType` has a `name` and a boolean `status`.

## Feature areas

- **Auth** (`AuthController` + `AuthService`): register / login / logout, issuing
  Sanctum tokens with configurable expiration.
- **Exercises** (`ExerciseController` + `ExerciseService`): full CRUD, scoped to
  the authenticated user.
- **Exercise types** (`ExerciseTypeController` + `ExerciseTypeService`).
- **Users** (`UserController` + `UserService`).
- **Dashboard** (`DashboardController` + `SummaryService`): weight/volume
  progress summary.
- **AI** (`AiController` + `AiService`): progression recommendation and
  natural-language analysis. See `ai.md`.

## Database & tests

- Dev and test both use PostgreSQL (SQLite driver is unavailable in this env).
- Tests are isolated in the `gym_test` Postgres schema via `DB_SEARCH_PATH`
  (configured in `phpunit.xml`), so they never touch dev data in `public`.
