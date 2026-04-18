<?php

namespace App\Http\Controllers\Settlement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settlement\StoreSettlementRequest;
use App\Http\Resources\Settlement\SettlementResource;
use App\Services\ActivityLogService;
use App\Services\SettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettlementController extends Controller
{
    public function __construct(
        private readonly SettlementService $service
    ) {}

    public function store(int $violationId, StoreSettlementRequest $request)
    {
        $employeeId = Auth::id();

        $settlement = $this->service->settle(
            $violationId,
            $employeeId,
            $request->validated()['description']
        );
        app(ActivityLogService::class)->log(
            'settlement.created',
            'settlements',
            "Membuat settlement untuk violation #{$violationId}.",
            [
                'settlement_id' => $settlement->id,
                'violation_id' => $violationId,
            ]
        );

        return (new SettlementResource($settlement))
            ->additional([
                'message' => 'Pelanggaran berhasil diselesaikan.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function index(Request $request)
    {
        $settlements = $this->service->getAll([
            'search'   => $request->get('search'),
            'per_page' => $request->get('per_page', 10),
        ]);

        return SettlementResource::collection($settlements);
    }

    public function show(int $id)
    {
        $settlement = $this->service->getById($id);

        return new SettlementResource($settlement);
    }
}
