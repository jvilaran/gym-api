<?php

namespace App\Services;

use App\DTOs\ExerciseDto;
use App\Models\Exercise;

class ExerciseService
{
    public function index() 
    {
        $result = Exercise::all();

        if($result->isEmpty()) {
            $data = [
                'message' => 'No hay ejercicios disponibles',
                'status' => false
            ];

            return response()->json($data, 404);
        }

        $data = [
            'exercises' => $result,
            'status' => true
        ];

        return response()->json($data, 200);
    }

    public function store(ExerciseDto $dto) 
    {
        $result = Exercise::create([
            'user_id' => $dto->userId,
            'exercise_type_id' => $dto->exerciseTypeId,
            'weight' => $dto->weight,
            'series' => $dto->series,
            'reps' => $dto->reps
        ]);

        if(!$result) {
            $data = [
                'message' => 'Error al crear el ejercicio',
                'status' => false
            ];
            
            return response()->json($data, 500);
        }

        $data = [
            'exercise' => $result,
            'status' => true
        ];

        return response()->json($data, 201);
    }
}
