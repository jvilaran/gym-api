<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\UserDto;
use App\DTOs\UserLoginDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserRequest;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Services\AuthService;

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
}
