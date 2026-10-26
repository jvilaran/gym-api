# API Reference

Base path: `/api`. All responses are JSON and include a `status` boolean.

## Authentication

Token-based via Laravel Sanctum. Register or log in to receive a token, then
send it as `Authorization: Bearer <token>` on protected routes.

### POST `/api/auth/register`

Body: `name`, `email`, `password`.

```json
{
  "user": { "id": 1, "name": "Ana", "email": "ana@example.com" },
  "token": "1|abc...",
  "expires_at": "2026-10-27T12:00:00.000000Z",
  "status": true
}
```

### POST `/api/auth/login`

Body: `email`, `password`. Returns the same shape as register. Invalid
credentials return `401` with `{ "message": "Credenciales inválidas", "status": false }`.

### POST `/api/auth/logout`  *(auth required)*

Revokes the current token. Returns `{ "message": "Sesión cerrada", "status": true }`.

---

All routes below require `Authorization: Bearer <token>`. Missing/invalid token
returns `401`.

## Exercises

Scoped to the authenticated user. `user_id` is taken from the token and is
ignored if sent in the body.

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/exercises` | List the user's exercises (with type) |
| POST | `/api/exercises` | Create an exercise |
| GET | `/api/exercises/{id}` | Show one of the user's exercises |
| PUT | `/api/exercises/{id}` | Update one of the user's exercises |
| DELETE | `/api/exercises/{id}` | Delete one of the user's exercises |

### POST `/api/exercises`

Body:

```json
{ "exercise_type_id": 1, "weight": 60, "series": 4, "reps": 8 }
```

Response `201`:

```json
{
  "exercise": {
    "id": 10, "user_id": 1, "exercise_type_id": 1,
    "weight": "60.00", "series": 4, "reps": 8
  },
  "status": true
}
```

Validation errors return `422` with `{ "message": "Error de validacion", "errors": {...} }`.
Accessing an exercise that is not yours returns `404`.

## Dashboard

### GET `/api/dashboard/summary`

Weight/volume progress for the authenticated user. Volume = `series * reps * weight`.

```json
{
  "summary": {
    "total_exercises": 2,
    "last_workout": "2026-10-26",
    "total_volume": 4400,
    "by_exercise_type": [
      {
        "exercise_type_id": 1,
        "exercise_type": "Press de banca",
        "sessions": 2,
        "max_weight": 60,
        "total_volume": 4400,
        "progression": [
          { "date": "2026-10-19", "weight": 50, "volume": 2000 },
          { "date": "2026-10-26", "weight": 60, "volume": 2400 }
        ],
        "trend": { "weight_change": 10, "volume_change": 400, "direction": "up" }
      }
    ]
  },
  "status": true
}
```

Returns `404` when the user has no exercises yet.

## AI

The numbers are computed deterministically; the `message` is natural-language
text produced by the configured AI provider (default `fake`). The `provider`
field reports which driver generated the text. See `ai.md`.

### GET `/api/ai/recommendation`

Next-session progression recommendation per exercise type.

```json
{
  "recommendations": [
    {
      "exercise_type_id": 1,
      "exercise_type": "Sentadilla",
      "current": { "weight": 50, "series": 4, "reps": 10 },
      "recommended": { "weight": 52.5, "series": 4, "reps": 8 },
      "rationale": "Alcanzaste el tope de repeticiones, sube el peso y baja las reps."
    }
  ],
  "message": "Resumen del entrenador: ...",
  "provider": "fake",
  "status": true
}
```

Returns `404` when there is no history.

### GET `/api/ai/analysis`

Natural-language analysis built from the dashboard summary.

```json
{
  "summary": { "...": "same shape as /dashboard/summary" },
  "message": "Resumen del entrenador: ...",
  "provider": "fake",
  "status": true
}
```

## Catalog

Read-only browse of the external exercise catalog (ExerciseGymGifsDB), proxied
and cached by the backend. See `catalog.md`. `lang` query param is `es`
(default) or `en`.

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/catalog/muscles` | Muscle groups with exercise counts |
| GET | `/api/catalog/muscles/{muscle}` | Exercises for a muscle group |
| GET | `/api/catalog/exercises` | All exercises (`?muscle=&equipment=` filters) |
| GET | `/api/catalog/exercises/{muscle}/{slug}` | Single exercise detail |

Each exercise item includes `id` (`"muscle/slug"`), `slug`, `name`, `muscle`,
`bodyPart`, `equipment`, `category`, `secondaryMuscles[]`, `instructions[]` and
`gifUrl`. Unknown muscle → `404`; unsupported lang → `422`; CDN down → `503`.

### GET `/api/catalog/muscles/{muscle}?lang=es`

```json
{
  "muscle": "biceps",
  "count": 151,
  "exercises": [
    {
      "id": "biceps/barbell-curl",
      "slug": "barbell-curl",
      "name": "Curl con barra",
      "muscle": "biceps",
      "equipment": "barbell",
      "gifUrl": "https://cdn.jsdelivr.net/gh/JahelCuadrado/ExerciseGymGifsDB@v1.1.0/biceps/barbell-curl.gif"
    }
  ],
  "status": true
}
```

### POST `/api/exercise-types/from-catalog`

Find-or-create an exercise type from a catalog item. Body:

```json
{ "catalog_id": "biceps/barbell-curl", "lang": "es" }
```

Response `201` (created) or `200` (already existed):

```json
{
  "exercise_type": {
    "id": 4, "name": "Curl con barra", "status": true,
    "catalog_id": "biceps/barbell-curl", "slug": "barbell-curl",
    "muscle": "biceps", "equipment": "barbell",
    "gif_url": "https://cdn.jsdelivr.net/gh/.../biceps/barbell-curl.gif"
  },
  "created": true,
  "status": true
}
```

Use the returned `exercise_type.id` as `exercise_type_id` when logging a set via
`POST /api/exercises`. Invalid `catalog_id` → `422`; not found in catalog →
`404`; CDN down → `503`.

## Exercise types & users

CRUD routes exist for `exercise-types` and `users`:

- `GET|POST /api/exercise-types`, `POST /api/exercise-types/from-catalog`,
  `GET|PUT|DELETE /api/exercise-types/{id}`
- `GET|POST /api/users`, `GET|PUT|DELETE /api/users/{id}`

`index`, `store` and `from-catalog` are implemented for exercise types; the full
user CRUD is implemented in `UserService`. The `show/update/destroy` handlers
for exercise types are currently stubs.
