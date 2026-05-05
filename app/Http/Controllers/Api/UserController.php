<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\DTOs\UserDto;
use App\Http\Requests\Api\UserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
