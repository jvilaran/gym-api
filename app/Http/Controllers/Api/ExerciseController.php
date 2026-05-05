<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ExerciseDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExerciseRequest;
use App\Services\ExerciseService;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function __construct(protected ExerciseService $exerciseService){}

    public function index()
    {
        $result = $this->exerciseService->index();

        return $result;
    }

    public function store(ExerciseRequest $request)
    {
        $dto = ExerciseDto::fromRequest($request);
        
        return $this->exerciseService->store($dto);
    }

    public function show(string $id)
    {

    }

    public function update(Request $request, string $id)
    {

    }

    public function destroy(string $id)
    {

    }
}
