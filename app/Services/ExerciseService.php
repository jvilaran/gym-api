<?php

namespace App\Services;

use App\DTOs\ExerciseDto;
use App\DTOs\ExerciseUpdateDto;
use App\Models\Exercise;
use App\Models\User;

class ExerciseService
{
    public function index(User $user)
    {
        $result = $user->exercises()->with('exerciseType')->get();

        if ($result->isEmpty()) {
            $data = [
                'message' => 'No hay ejercicios disponibles',
                'status' => false,
            ];

            return response()->json($data, 404);
        }

        $data = [
            'exercises' => $result,
            'status' => true,
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
            'reps' => $dto->reps,
        ]);

        if (! $result) {
            $data = [
                'message' => 'Error al crear el ejercicio',
                'status' => false,
            ];

            return response()->json($data, 500);
        }

        $data = [
            'exercise' => $result,
            'status' => true,
        ];

        return response()->json($data, 201);
    }

    public function show(User $user, int $id)
    {
        $exercise = $user->exercises()->with('exerciseType')->find($id);

        if (! $exercise) {
            $data = [
                'message' => 'Ejercicio no encontrado',
                'status' => false,
            ];

            return response()->json($data, 404);
        }

        $data = [
            'exercise' => $exercise,
            'status' => true,
        ];

        return response()->json($data, 200);
    }

    public function update(User $user, int $id, ExerciseUpdateDto $dto)
    {
        $exercise = $user->exercises()->find($id);

        if (! $exercise) {
            $data = [
                'message' => 'Ejercicio no encontrado',
                'status' => false,
            ];

            return response()->json($data, 404);
        }

        if ($dto->exerciseTypeId !== null) {
            $exercise->exercise_type_id = $dto->exerciseTypeId;
        }
        if ($dto->weight !== null) {
            $exercise->weight = $dto->weight;
        }
        if ($dto->series !== null) {
            $exercise->series = $dto->series;
        }
        if ($dto->reps !== null) {
            $exercise->reps = $dto->reps;
        }

        if (! $exercise->save()) {
            $data = [
                'message' => 'Error al actualizar el ejercicio',
                'status' => false,
            ];

            return response()->json($data, 500);
        }

        $data = [
            'exercise' => $exercise,
            'status' => true,
        ];

        return response()->json($data, 200);
    }

    public function destroy(User $user, int $id)
    {
        $exercise = $user->exercises()->find($id);

        if (! $exercise) {
            $data = [
                'message' => 'Ejercicio no encontrado',
                'status' => false,
            ];

            return response()->json($data, 404);
        }

        if (! $exercise->delete()) {
            $data = [
                'message' => 'Error al eliminar el ejercicio',
                'status' => false,
            ];

            return response()->json($data, 500);
        }

        $data = [
            'message' => 'Ejercicio eliminado correctamente',
            'status' => true,
        ];

        return response()->json($data, 200);
    }
}
