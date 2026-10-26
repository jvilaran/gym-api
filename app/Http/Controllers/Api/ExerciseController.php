<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ExerciseDto;
use App\DTOs\ExerciseUpdateDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExerciseRequest;
use App\Http\Requests\Api\ExerciseUpdateRequest;
use App\Services\ExerciseService;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function __construct(protected ExerciseService $exerciseService) {}

    public function index(Request $request)
    {
        return $this->exerciseService->index($request->user());
    }

    public function store(ExerciseRequest $request)
    {
        $dto = ExerciseDto::fromRequest($request, $request->user()->id);

        return $this->exerciseService->store($dto);
    }

    public function show(Request $request, string $id)
    {
        return $this->exerciseService->show($request->user(), (int) $id);
    }

    public function update(ExerciseUpdateRequest $request, string $id)
    {
        $dto = ExerciseUpdateDto::fromRequest($request);

        return $this->exerciseService->update($request->user(), (int) $id, $dto);
    }

    public function destroy(Request $request, string $id)
    {
        return $this->exerciseService->destroy($request->user(), (int) $id);
    }
}
