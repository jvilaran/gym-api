<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Reads the external exercise catalog (ExerciseGymGifsDB) from the jsDelivr
 * CDN and caches it. The data is static and version-pinned, so a long TTL is
 * safe. No API key is required.
 */
class CatalogService
{
    public function defaultLang(): string
    {
        return (string) config('catalog.default_lang', 'es');
    }

    public function isSupportedLang(string $lang): bool
    {
        return in_array($lang, (array) config('catalog.supported_langs', ['es', 'en']), true);
    }

    public function isKnownMuscle(string $muscle): bool
    {
        return in_array($muscle, (array) config('catalog.muscles', []), true);
    }

    /**
     * List of muscle groups with exercise counts.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function muscles(string $lang): ?array
    {
        return $this->get("api/{$lang}/muscles.json");
    }

    /**
     * Exercises for a single muscle group.
     *
     * @return array<string, mixed>|null
     */
    public function byMuscle(string $lang, string $muscle): ?array
    {
        return $this->get("api/{$lang}/muscles/{$muscle}.json");
    }

    /**
     * All exercises for a language.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function all(string $lang): ?array
    {
        return $this->get("api/{$lang}/exercises.json");
    }

    /**
     * Detail for a single exercise.
     *
     * @return array<string, mixed>|null
     */
    public function show(string $lang, string $muscle, string $slug): ?array
    {
        return $this->get("api/{$lang}/exercises/{$muscle}/{$slug}.json");
    }

    /**
     * Look up a single exercise by its catalog id ("muscle/slug").
     *
     * @return array<string, mixed>|null
     */
    public function findById(string $lang, string $catalogId): ?array
    {
        if (! str_contains($catalogId, '/')) {
            return null;
        }

        [$muscle, $slug] = explode('/', $catalogId, 2);

        if (! $this->isKnownMuscle($muscle)) {
            return null;
        }

        return $this->show($lang, $muscle, $slug);
    }

    /**
     * Fetch + cache a CDN path. Returns the decoded JSON, or null on 404,
     * or throws ConnectionException if the CDN is unreachable.
     *
     * @return array<mixed>|null
     */
    private function get(string $path): ?array
    {
        $url = $this->url($path);
        $ttl = (int) config('catalog.cache_ttl', 604800);

        $cached = Cache::remember("catalog:{$path}", $ttl, function () use ($url): array {
            $response = Http::timeout((int) config('catalog.timeout', 10))->get($url);

            if ($response->status() === 404) {
                return ['__status' => 404];
            }

            $response->throw();

            return ['__status' => 200, 'data' => $response->json()];
        });

        if (($cached['__status'] ?? null) === 404) {
            return null;
        }

        return $cached['data'] ?? null;
    }

    private function url(string $path): string
    {
        $base = rtrim((string) config('catalog.base_url'), '/');
        $version = (string) config('catalog.version');

        return "{$base}@{$version}/{$path}";
    }
}
