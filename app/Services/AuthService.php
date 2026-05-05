<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\UserDto;
use App\DTOs\UserLoginDto;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function register(UserDto $dto)
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => bcrypt($dto->password),
        ]);

        if(!$user) {
            $data = [
                'message' => 'Error al crear el usuario',
                'status' => false
            ];

            return response()->json($data, 500);
        }

        $data = [
            'user' => $user,
            'token' => $user->createToken("API TOKEN")->plainTextToken,
            'status' => true
        ];

        return response()->json($data, 201);
    }

    public function login(UserLoginDto $dto)
    {
        if (!Auth::attempt(['email' => $dto->email, 'password' => $dto->password])) {
            $data = [
                'message' => 'Credenciales inválidas',
                'status' => false
            ];
            
            return response()->json($data, 401);
        }

        $user = User::where('email', $dto->email)->first();

        $data = [
            'user' => $user,
            'token' => $user->createToken("API TOKEN")->plainTextToken,
            'status' => true
        ];

        return response()->json($data, 200);
    }
}
