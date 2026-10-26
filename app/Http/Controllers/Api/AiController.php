<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(protected AiService $aiService) {}

    public function recommendation(Request $request)
    {
        return $this->aiService->recommendation($request->user());
    }

    public function analysis(Request $request)
    {
        return $this->aiService->analysis($request->user());
    }
}
