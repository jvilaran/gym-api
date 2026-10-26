<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SummaryService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected SummaryService $summaryService) {}

    public function summary(Request $request)
    {
        return $this->summaryService->forUser($request->user());
    }
}
