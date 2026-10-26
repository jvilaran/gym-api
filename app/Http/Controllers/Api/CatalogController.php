<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected CatalogService $catalog) {}

    public function muscles(Request $request)
    {
        $lang = $this->lang($request);
        if ($lang === null) {
            return $this->invalidLang();
        }

        return $this->guard(function () use ($lang) {
            $muscles = $this->catalog->muscles($lang);

            return response()->json([
                'muscles' => $muscles ?? [],
                'status' => true,
            ], 200);
        });
    }

    public function byMuscle(Request $request, string $muscle)
    {
        $lang = $this->lang($request);
        if ($lang === null) {
            return $this->invalidLang();
        }

        if (! $this->catalog->isKnownMuscle($muscle)) {
            return response()->json([
                'message' => 'Grupo muscular no válido',
                'status' => false,
            ], 404);
        }

        return $this->guard(function () use ($lang, $muscle) {
            $data = $this->catalog->byMuscle($lang, $muscle);

            if ($data === null) {
                return response()->json([
                    'message' => 'Grupo muscular no encontrado',
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'muscle' => $data['muscle'] ?? $muscle,
                'count' => $data['count'] ?? count($data['exercises'] ?? []),
                'exercises' => $data['exercises'] ?? [],
                'status' => true,
            ], 200);
        });
    }

    public function exercises(Request $request)
    {
        $lang = $this->lang($request);
        if ($lang === null) {
            return $this->invalidLang();
        }

        return $this->guard(function () use ($request, $lang) {
            $muscle = $request->query('muscle');

            $exercises = $muscle
                ? ($this->catalog->byMuscle($lang, (string) $muscle)['exercises'] ?? [])
                : ($this->catalog->all($lang) ?? []);

            $equipment = $request->query('equipment');
            if ($equipment) {
                $exercises = array_values(array_filter(
                    $exercises,
                    fn (array $e) => ($e['equipment'] ?? null) === $equipment
                ));
            }

            return response()->json([
                'exercises' => $exercises,
                'count' => count($exercises),
                'status' => true,
            ], 200);
        });
    }

    public function show(Request $request, string $muscle, string $slug)
    {
        $lang = $this->lang($request);
        if ($lang === null) {
            return $this->invalidLang();
        }

        if (! $this->catalog->isKnownMuscle($muscle)) {
            return response()->json([
                'message' => 'Grupo muscular no válido',
                'status' => false,
            ], 404);
        }

        return $this->guard(function () use ($lang, $muscle, $slug) {
            $exercise = $this->catalog->show($lang, $muscle, $slug);

            if ($exercise === null) {
                return response()->json([
                    'message' => 'Ejercicio no encontrado',
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'exercise' => $exercise,
                'status' => true,
            ], 200);
        });
    }

    private function lang(Request $request): ?string
    {
        $lang = (string) $request->query('lang', $this->catalog->defaultLang());

        return $this->catalog->isSupportedLang($lang) ? $lang : null;
    }

    private function invalidLang()
    {
        return response()->json([
            'message' => 'Idioma no soportado',
            'status' => false,
        ], 422);
    }

    /**
     * Run a catalog read and translate a CDN outage into a 503.
     */
    private function guard(callable $fn)
    {
        try {
            return $fn();
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'No se pudo consultar el catálogo de ejercicios',
                'status' => false,
            ], 503);
        }
    }
}
