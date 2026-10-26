# gym-api

REST API (JSON only) for a gym progress tracker. Clients register exercises
(weight, series, reps) by exercise type and get a progress dashboard plus AI
features (progression recommendation and natural-language analysis). The
frontend lives in a separate project and consumes this API with Sanctum tokens.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed   # PostgreSQL
php artisan serve
```

## Testing

```bash
# Tests run against PostgreSQL in an isolated schema (gym_test).
php artisan test
```

If the test schema does not exist yet:

```sql
CREATE SCHEMA IF NOT EXISTS gym_test;
```

## Documentation

- [AGENTS.md](AGENTS.md) — context and rules for AI assistants
- [docs/architecture.md](docs/architecture.md) — layers, domain model
- [docs/api.md](docs/api.md) — endpoints with request/response examples
- [docs/frontend-integration.md](docs/frontend-integration.md) — guide for the frontend project
- [docs/catalog.md](docs/catalog.md) — external exercise catalog (GIFs) integration
- [docs/conventions.md](docs/conventions.md) — coding conventions
- [docs/ai.md](docs/ai.md) — the AI layer and how to connect a real provider

## Stack

Laravel 12 · PHP 8.2+ · Laravel Sanctum · PostgreSQL · PHPUnit 11
