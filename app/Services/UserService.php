<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\UserDto;

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
}
