<?php

namespace App\DTOs;

use App\Http\Requests\Auth\UserLoginRequest;

readonly class UserLoginDto 
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(UserLoginRequest $request): self {
        $validated = $request->validated();
        
        return new self(
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
