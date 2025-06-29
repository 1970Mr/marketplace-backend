<?php

namespace App\Http\Controllers\Api\V1\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Admin\Dashboard\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function __construct(readonly private AdminDashboardService $dashboardService)
    {
    }

    public function index(): JsonResponse
    {
        $dashboardData = $this->dashboardService->getDashboardData(Auth::guard('admin-api')->user());

        return response()->json([
            'data' => $dashboardData
        ]);
    }
}
