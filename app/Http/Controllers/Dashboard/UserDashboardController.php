<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\UserDashboardService;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function __construct(
        private UserDashboardService $dashboardService
    ) {}


    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page']);

        $data = $this->dashboardService->getDashboardData($filters);
        
        return response()->json($data);
    }
}
