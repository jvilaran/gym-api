# Exercise Catalog

Users don't have to invent exercise types by hand. The backend exposes a
catalog of 1300+ exercises (with GIFs, muscle, equipment, instructions) sourced
from **ExerciseGymGifsDB**, a static JSON API served over the jsDelivr CDN.

- Source: https://github.com/JahelCuadrado/ExerciseGymGifsDB
- No API key required. Data is version-pinned, so the backend caches it.
- Multilingual: Spanish (`es`, default) and English (`en`).

## How it works

```
Frontend → GET /api/catalog/*            (browse catalog, backend proxies + caches the CDN)
Frontend → POST /api/exercise-types/from-catalog   (user picks one → find-or-create an exercise_type)
Frontend → POST /api/exercises           (log a set using the returned exercise_type_id)
```

The backend fetches from the CDN (never the browser), caches responses in the
app cache for `CATALOG_CACHE_TTL` seconds (default 7 days), and serves them
through your own API. When a user picks a catalog exercise, the backend
`firstOrCreate`s an `exercise_type` keyed by `catalog_id`, so the same exercise
is never duplicated. The user's `exercise` records keep referencing
`exercise_type_id` exactly as before — only the type now also carries catalog
metadata.

## exercise_types catalog columns

Manually created types leave these null; catalog-created types fill them in:

- `catalog_id` — unique, form `"muscle/slug"` (e.g. `biceps/barbell-curl`)
- `slug`, `muscle`, `equipment`, `gif_url`

## Configuration (`config/catalog.php`)

| Env | Default | Purpose |
| --- | ------- | ------- |
| `CATALOG_BASE_URL` | jsDelivr gh URL | CDN base |
| `CATALOG_VERSION` | `v1.1.0` | pinned release tag |
| `CATALOG_DEFAULT_LANG` | `es` | default language |
| `CATALOG_CACHE_TTL` | `604800` | cache seconds (7 days) |
| `CATALOG_TIMEOUT` | `10` | HTTP timeout seconds |

To upgrade the catalog, bump `CATALOG_VERSION` to a newer tag; the cache key
includes the path and the version is part of the URL, so new data is fetched.

## Caching & resilience

- Successful and 404 responses are cached (404 as a short "not found" marker).
- If the CDN is unreachable, catalog endpoints return `503` with
  `{ status: false }` instead of failing hard.

## Attribution / licensing

The GIFs are third-party content collected from the internet; the source repo
does not own their copyright and distributes them as an organization layer only.
Credit ExerciseGymGifsDB and review licensing before using the GIFs in
production.
