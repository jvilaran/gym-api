<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\UserDto;
use App\DTOs\UserLoginDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserRequest;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService){}

    public function register(UserRequest $request)
    {
        $dto = UserDto::fromRequest($request);
        
        return $this->authService->register($dto);
    }

    public function login(UserLoginRequest $request)
    {
        $dto = UserLoginDto::fromRequest($request);

        return $this->authService->login($dto);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Sesión cerrada', 'status' => true], 200);
        }

        return response()->json(['message' => 'No se encontró token activo', 'status' => false], 400);
    }
}
