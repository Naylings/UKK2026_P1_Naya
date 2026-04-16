<?php

namespace App\Http\Controllers\Violation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Violation\ViolationResource;
use App\Services\ViolationService;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    public function __construct(
        private readonly ViolationService $service
    ) {}

    public function index(Request $request)
    {
        $violations = $this->service->getAll([
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'per_page' => $request->get('per_page', 10),
        ]);

        return ViolationResource::collection($violations);
    }

    public function show(int $id)
    {
        return new ViolationResource($this->service->getById($id));
    }
}
