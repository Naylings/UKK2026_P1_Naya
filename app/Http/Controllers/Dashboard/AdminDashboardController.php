<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;

class AdminDashboardController extends Controller
{
    public function __construct(
        private AdminDashboardService $service
    ) {}

    public function index()
    {
        return response()->json(
            $this->service->getDashboardData()
        );
    }
}