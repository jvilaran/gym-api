# AGENTS.md

Context for AI assistants working on **gym-api**. Read this before making changes.

## What this project is

A REST API (JSON only) for a gym progress tracker. Clients register exercises
(weight, series, reps) by exercise type; the API exposes auth, CRUD, a progress
dashboard, and AI features (progression recommendation + natural-language
analysis). There is **no frontend here** — the UI lives in a separate project
that consumes this API with Sanctum bearer tokens. Do not add Blade views or a
frontend build.

## Stack

- PHP 8.2+ (dev runs 8.4), Laravel 12
- Laravel Sanctum (token auth)
- PostgreSQL (dev + tests). SQLite is not used (no `pdo_sqlite` available).
- PHPUnit 11

## Architecture: layered, keep it consistent

Every write/read flows through the same layers. Match this pattern for new code:

```
Route -> Controller (thin) -> FormRequest (validation) -> DTO (readonly) -> Service (logic + JSON) -> Model
```

- **Controllers** (`app/Http/Controllers`) stay thin: build the DTO and delegate.
- **FormRequests** (`app/Http/Requests`) validate. On failure they return JSON
  422 via `failedValidation` with `{ message, errors }`.
- **DTOs** (`app/DTOs`) are `readonly` classes with a static `fromRequest()`.
- **Services** (`app/Services`) hold the logic and return `response()->json(...)`
  directly.
- **Models** (`app/Models`) define relations and casts.

## Response & language conventions

- JSON responses use the shape `{ ...data, "status": <bool> }`.
- User-facing API messages and seed data are in **Spanish** (existing codebase
  convention). Keep them in Spanish.
- Code, identifiers, comments, and docs are in English.
- Common status codes: 200 OK, 201 created, 401 unauthenticated, 404 not found,
  422 validation, 500 server error.

## Security rules

- Exercises are scoped to the authenticated user. Never accept `user_id` from
  the request body — take it from `$request->user()`.
- All resource routes sit behind `auth:sanctum`.

## AI layer

- All AI goes through the `App\Services\Ai\AiProvider` interface.
- Default driver is **`fake`** (deterministic, no network, no cost). Tests must
  keep using it. Real drivers (OpenAI/Anthropic/Gemini) are stubs enabled via
  `AI_PROVIDER` + the matching API key.
- Numbers are always computed by deterministic services
  (`ProgressionService`, `SummaryService`). The LLM only writes the prose.
  Do not let the model decide weights/reps.

## Exercise catalog

- Users pick exercises from an external catalog (ExerciseGymGifsDB) instead of
  typing them. The **backend** proxies + caches the static jsDelivr CDN
  (`config/catalog.php`); the frontend never calls the CDN for data.
- `CatalogService` fetches + caches; `CatalogController` exposes `/api/catalog/*`.
- Picking an exercise calls `POST /api/exercise-types/from-catalog`, which
  `firstOrCreate`s an `exercise_type` keyed by `catalog_id` (no duplicates) and
  stores name/slug/muscle/equipment/gif_url. User `exercise` records still
  reference `exercise_type_id`.
- Catalog tests must use `Http::fake()` — never hit the real CDN in tests.
- CDN unreachable → `503`; unknown muscle → `404`; bad lang → `422`.
- See `docs/catalog.md`.

## Running & testing

```bash
composer install
php artisan migrate:fresh --seed   # PostgreSQL (public schema)
php artisan test                   # uses the gym_test schema in Postgres
```

- Tests run against Postgres using an isolated schema (`DB_SEARCH_PATH=gym_test`
  in `phpunit.xml`) so they never touch dev data. The schema must exist:
  `CREATE SCHEMA IF NOT EXISTS gym_test;`
- Add tests for new features: unit for pure logic, feature for endpoints.
  Do not add tests unless the change warrants them.

## More docs

See `docs/` for architecture, API reference, conventions, and the AI layer.
