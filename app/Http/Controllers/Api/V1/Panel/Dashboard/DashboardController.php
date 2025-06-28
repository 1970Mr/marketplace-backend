<?php

namespace App\Http\Controllers\Api\V1\Panel\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Panel\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(readonly private DashboardService $dashboardService)
    {
    }

    public function index(): JsonResponse
    {
        $dashboardData = $this->dashboardService->getDashboardData(Auth::user());

        return response()->json([
            'data' => $dashboardData
        ]);
    }
}
