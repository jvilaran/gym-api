<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ExerciseTypeDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExerciseTypeRequest;
use App\Services\ExerciseTypeService;
use Illuminate\Http\Request;

class ExerciseTypeController extends Controller
{
    public function __construct(protected ExerciseTypeService $exerciseTypeService){}

    public function index()
    {
        $result = $this->exerciseTypeService->index();
        
        return $result;
    }

    public function store(ExerciseTypeRequest $request)
    {
        $dto = ExerciseTypeDto::fromRequest($request);
        
        return $this->exerciseTypeService->store($dto);
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
