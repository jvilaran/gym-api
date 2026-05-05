<?php

namespace App\Services;

use App\DTOs\ExerciseTypeDto;
use App\Models\ExerciseType;

class ExerciseTypeService
{
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
