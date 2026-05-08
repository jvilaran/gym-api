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

        $expiresAt = config('sanctum.expiration')
            ? now()->addMinutes(config('sanctum.expiration'))
            : now()->addHours(env('SANCTUM_TOKEN_EXPIRE_HOURS', 8));

        $tokenResult = $user->createToken("API TOKEN", ['*'], $expiresAt);

        $data = [
            'user' => $user,
            'token' => $tokenResult->plainTextToken,
            'expires_at' => $tokenResult->accessToken->expires_at,
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

        $expiresAt = config('sanctum.expiration')
            ? now()->addMinutes(config('sanctum.expiration'))
            : now()->addHours(env('SANCTUM_TOKEN_EXPIRE_HOURS', 8));

        $tokenResult = $user->createToken("API TOKEN", ['*'], $expiresAt);

        $data = [
            'user' => $user,
            'token' => $tokenResult->plainTextToken,
            'expires_at' => $tokenResult->accessToken->expires_at,
            'status' => true
        ];

        return response()->json($data, 200);
    }
}
