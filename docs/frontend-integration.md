# Frontend Integration Guide

How to consume **gym-api** from the separate frontend project.

## TL;DR

- Auth is **token-based** (bearer token), not cookie/session. No CSRF, no
  `/sanctum/csrf-cookie` call needed.
- Flow: `register` or `login` → store the returned `token` → send
  `Authorization: Bearer <token>` on every protected request.
- Every response has a `status` boolean. `status: false` means the operation
  did not succeed (check the HTTP code and `message`).
- All text/messages from the API are in Spanish.
- Base URL: `http://<host>/api` (e.g. `http://localhost:8000/api` in dev).

## Auth flow

1. **Register** `POST /api/auth/register` with `{ name, email, password }` → get
   `{ user, token, expires_at, status }`.
2. Or **Login** `POST /api/auth/login` with `{ email, password }` → same shape.
3. Save `token` (e.g. in memory + `localStorage`) and `expires_at`.
4. Send `Authorization: Bearer <token>` on all protected calls.
5. **Logout** `POST /api/auth/logout` (with the header) revokes the token.

Token expires after `expires_at` (default 8h). On a `401`, clear the stored
token and send the user back to login.

### Example (fetch)

```js
const API = "http://localhost:8000/api";

async function login(email, password) {
  const res = await fetch(`${API}/auth/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify({ email, password }),
  });
  const data = await res.json();
  if (!res.ok || !data.status) throw new Error(data.message ?? "Login falló");
  localStorage.setItem("token", data.token);
  return data.user;
}

function authHeaders() {
  return {
    Accept: "application/json",
    "Content-Type": "application/json",
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  };
}
```

## Response conventions

- Success list: `{ "<resource>": [...], "status": true }`
- Success single: `{ "<resource>": {...}, "status": true }`
- Empty list / not found: HTTP `404`, `{ "message": "...", "status": false }`
- Validation error: HTTP `422`, `{ "message": "Error de validacion", "errors": { field: [msg] } }`
- Unauthenticated: HTTP `401`

Handle `404` on list endpoints as "no data yet" (empty state), not a hard error.

## Endpoints the frontend needs

### Auth (public)
| Method | Path | Body |
| ------ | ---- | ---- |
| POST | `/api/auth/register` | `name, email, password` |
| POST | `/api/auth/login` | `email, password` |
| POST | `/api/auth/logout` | — (auth) |

### Exercises (auth, scoped to the logged-in user)
| Method | Path | Body |
| ------ | ---- | ---- |
| GET | `/api/exercises` | — |
| POST | `/api/exercises` | `exercise_type_id, weight, series, reps` |
| GET | `/api/exercises/{id}` | — |
| PUT | `/api/exercises/{id}` | any of `exercise_type_id, weight, series, reps` |
| DELETE | `/api/exercises/{id}` | — |

Do **not** send `user_id` — it is taken from the token and ignored if sent.

### Exercise types (auth)
| Method | Path | Body |
| ------ | ---- | ---- |
| GET | `/api/exercise-types` | — |
| POST | `/api/exercise-types` | `name` |

Use `GET /api/exercise-types` to populate the dropdown in the "add exercise"
form. (`show/update/destroy` for types are not implemented yet.)

### Catalog (auth) — browse exercises with GIFs

The backend proxies/caches an external catalog so users pick an exercise instead
of typing one. `lang` is `es` (default) or `en`.

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/catalog/muscles` | muscle groups + counts |
| GET | `/api/catalog/muscles/{muscle}` | exercises for a muscle (with `gifUrl`) |
| GET | `/api/catalog/exercises?muscle=&equipment=` | all / filtered |
| GET | `/api/catalog/exercises/{muscle}/{slug}` | one exercise detail |
| POST | `/api/exercise-types/from-catalog` | pick one → get an `exercise_type` |

Flow for "pick from catalog and log a set":

1. `GET /api/catalog/muscles` → show muscle groups.
2. `GET /api/catalog/muscles/{muscle}` → render the list with `gifUrl` images.
3. User picks one → `POST /api/exercise-types/from-catalog` with
   `{ catalog_id: exercise.id }` → response has `exercise_type.id`.
4. `POST /api/exercises` with that `exercise_type_id` + weight/series/reps.

`catalog_id` is the catalog item's `id` field (e.g. `"biceps/barbell-curl"`).
Render GIFs directly from `gifUrl` (`<img src={gifUrl} />`) — no auth needed on
the CDN image itself.

### Dashboard (auth)
| Method | Path |
| ------ | ---- |
| GET | `/api/dashboard/summary` |

### AI (auth)
| Method | Path |
| ------ | ---- |
| GET | `/api/ai/recommendation` |
| GET | `/api/ai/analysis` |

See `docs/api.md` for full request/response examples of each.

## Validation rules (so the form matches the API)

- register: `name` unique, `email` valid+unique, `password` min 4
- login: `email` valid, `password` min 4
- exercise create: `exercise_type_id` required + must exist, `weight` numeric ≥ 0,
  `series` integer ≥ 1, `reps` integer ≥ 1
- exercise update: same fields but all optional

Mirror these client-side for instant feedback, but always surface the API's
`errors` object too (it is the source of truth).

## Screens to build

1. **Login / Register** — forms hitting `/auth/login` and `/auth/register`; store token.
2. **Dashboard (summary)** — call `/dashboard/summary`. Show:
   - headline cards: `total_exercises`, `last_workout`, `total_volume`
   - per exercise type: `max_weight`, `total_volume`, `trend.direction`
     (up/down/stable) and a line chart from `progression[]`
     (`date` vs `weight` and/or `volume`).
   - Optionally a panel for `/ai/analysis` → render `message` as a text summary.
3. **Add data (exercise form)** — dropdown from `/exercise-types`, inputs for
   weight/series/reps, POST to `/exercises`. Optionally a quick-add for a new
   exercise type via `POST /exercise-types`.
3b. **Exercise picker (catalog)** — browse muscle groups → list exercises with
   their GIFs (`gifUrl`) → on pick, call `/exercise-types/from-catalog` to get an
   `exercise_type`, then prefill the add-exercise form with its id. See the
   Catalog section above and `catalog.md`.
4. **Exercise list / history** — `/exercises` with edit (PUT) and delete.
5. **AI coach (optional screen or widget)** — `/ai/recommendation` shows the
   suggested next weight/series/reps per type plus the motivational `message`.

## Data shapes to model on the client

```ts
type Exercise = {
  id: number;
  user_id: number;
  exercise_type_id: number;
  weight: string;   // decimal serialized as string, e.g. "60.00"
  series: number;
  reps: number;
  exerciseType?: { id: number; name: string; status: boolean };
};

type SummaryType = {
  exercise_type_id: number;
  exercise_type: string;
  sessions: number;
  max_weight: number;
  total_volume: number;
  progression: { date: string; weight: number; volume: number }[];
  trend: { weight_change: number; volume_change: number; direction: "up" | "down" | "stable" };
};
```

Note: `weight` comes back as a **string** ("60.00") on an `Exercise` because of
the decimal cast. Parse it with `Number()` before doing math on the client.

## CORS (needs a small backend change)

The frontend runs on a different origin, so the API must send CORS headers.
This is **not configured yet**. Before integrating, the backend should publish
and set `config/cors.php` to allow the frontend origin, e.g.:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000'], // the frontend dev URL
'allowed_headers' => ['*'],
'supports_credentials' => false, // token auth, no cookies
```

Until that is in place, browser requests from the frontend will be blocked by
CORS even though the API itself works (tools like curl/Postman are unaffected).
