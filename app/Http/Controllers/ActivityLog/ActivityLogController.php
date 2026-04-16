<?php

namespace App\Http\Controllers\ActivityLog;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLog\ActivityLogResource;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    public function index(Request $request)
    {
        $logs = $this->activityLogService->getAll([
            'search' => $request->get('search'),
            'module' => $request->get('module'),
            'action' => $request->get('action'),
            'per_page' => $request->get('per_page', 10),
        ]);

        return ActivityLogResource::collection($logs);
    }

    public function show(int $id)
    {
        return new ActivityLogResource($this->activityLogService->getById($id));
    }
}
