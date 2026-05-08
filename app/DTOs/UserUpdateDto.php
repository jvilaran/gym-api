<?php

namespace App\DTOs;

use App\Http\Requests\Api\UserUpdateRequest;

readonly class UserUpdateDto 
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(UserUpdateRequest $request): self {
        $validated = $request->validated();
        
        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
