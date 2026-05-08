<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\DTOs\UserDto;
use App\DTOs\UserUpdateDto;
use App\Http\Requests\Api\UserRequest;
use App\Http\Requests\Api\UserUpdateRequest;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(protected UserService $userService){}

    public function index()
    {
        $result = $this->userService->index();

        return $result;
    }

    public function store(UserRequest $request)
    {
        $dto = UserDto::fromRequest($request);
        
        return $this->userService->store($dto);
    }

    public function show(int $id)
    {
        $result = $this->userService->show($id);

        return $result;
    }

    public function update(UserUpdateRequest $request, int $id)
    {
        $dto = UserUpdateDto::fromRequest($request);

        return $this->userService->update($id, $dto);
    }

    public function destroy(int $id)
    {
        return $this->userService->destroy($id);
    }
}
