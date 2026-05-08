<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\UserDto;
use App\DTOs\UserUpdateDto;

class UserService
{
    public function index() 
    {
        $result = User::all();

        if($result->isEmpty()) {
            $data = [
                'message' => 'No hay usuarios disponibles',
                'status' => false
            ];

            return response()->json($data, 404);
        }

        $data = [
            'users' => $result,
            'status' => true
        ];

        return response()->json($data, 200);
    }

    public function store(UserDto $dto)
    {
        $result = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => bcrypt($dto->password),
        ]);

        if(!$result) {
            $data = [
                'message' => 'Error al crear el usuario',
                'status' => false
            ];

            return response()->json($data, 500);
        }

        $data = [
            'user' => $result,
            'status' => true
        ];

        return response()->json($data, 201);
    }

    public function show(int $id)
    {
        $result = User::find($id);

        if(!$result) {
            $data = [
                'message' => 'Usuario no encontrado',
                'status' => false
            ];

            return response()->json($data, 404);
        }

        $data = [
            'user' => $result,
            'status' => true
        ];

        return response()->json($data, 200);
    }

    public function update(int $id, UserUpdateDto $dto)
    {
        $user = User::find($id);

        if(!$user) {
            $data = [
                'message' => 'Usuario no encontrado',
                'status' => false
            ];

            return response()->json($data, 404);
        }

        if($dto->name) {
            $user->name = $dto->name;
        }
        if($dto->email) {
            $user->email = $dto->email;
        }
        if($dto->password) {
            $user->password = bcrypt($dto->password);
        }

        if(!$user->save()) {
            $data = [
                'message' => 'Error al actualizar el usuario',
                'status' => false
            ];

            return response()->json($data, 500);
        }

        $data = [
            'user' => tap($user),
            'status' => true
        ];

        return response()->json($data, 200);
    }

    public function destroy(int $id)
    {
        $user = User::find($id);

        if(!$user) {
            $data = [
                'message' => 'Usuario no encontrado',
                'status' => false
            ];

            return response()->json($data, 404);
        }

        if(!$user->delete()) {
            $data = [
                'message' => 'Error al eliminar el usuario',
                'status' => false
            ];

            return response()->json($data, 500);
        }

        $data = [
            'message' => 'Usuario eliminado correctamente',
            'status' => true
        ];

        return response()->json($data, 200);
    }
}
