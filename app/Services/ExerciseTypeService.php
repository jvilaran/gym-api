<?php

namespace App\Services;

use App\DTOs\ExerciseTypeDto;
use App\Models\ExerciseType;
use Illuminate\Http\Client\ConnectionException;

class ExerciseTypeService
{
    public function __construct(protected CatalogService $catalog) {}

    /**
     * Find or create an exercise type from a catalog item. Picking the same
     * catalog exercise twice reuses the existing type (keyed by catalog_id).
     */
    public function fromCatalog(string $catalogId, ?string $lang = null)
    {
        $lang ??= $this->catalog->defaultLang();

        $existing = ExerciseType::where('catalog_id', $catalogId)->first();

        if ($existing) {
            return response()->json([
                'exercise_type' => $existing,
                'created' => false,
                'status' => true,
            ], 200);
        }

        try {
            $item = $this->catalog->findById($lang, $catalogId);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'No se pudo consultar el catálogo de ejercicios',
                'status' => false,
            ], 503);
        }

        if ($item === null) {
            return response()->json([
                'message' => 'Ejercicio del catálogo no encontrado',
                'status' => false,
            ], 404);
        }

        $type = ExerciseType::create([
            'name' => $item['name'] ?? $catalogId,
            'status' => true,
            'catalog_id' => $catalogId,
            'slug' => $item['slug'] ?? null,
            'muscle' => $item['muscle'] ?? null,
            'equipment' => $item['equipment'] ?? null,
            'gif_url' => $item['gifUrl'] ?? null,
        ]);

        return response()->json([
            'exercise_type' => $type,
            'created' => true,
            'status' => true,
        ], 201);
    }

    public function index() 
    {
        $result = ExerciseType::all();

        if($result->isEmpty()) {

            $data = [
                'message' => 'No hay tipos de ejercicios disponibles',
                'status' => false
            ];

            return response()->json($data, 404);
        }

        $data = [
            'exercise_types' => $result,
            'status' => true
        ];

        return response()->json($data, 200);
    }

    public function store(ExerciseTypeDto $dto) 
    {
        $result = ExerciseType::create([
            'name' => $dto->name
        ]);

        if(!$result) {
            $data = [
                'message' => 'Error al crear el tipo de ejercicio',
                'status' => false
            ];
            
            return response()->json($data, 500);
        }

        $data = [
            'exercise_types' => $result,
            'status' => true
        ];

        return response()->json($data, 201);
    }
}
