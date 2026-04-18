<?php

namespace App\Http\Controllers\Return;

use App\Http\Controllers\Controller;
use App\Http\Requests\Return\StoreReturn;
use App\Http\Requests\Return\ReviewReturn;
use App\Http\Resources\Return\ReturnResource;
use App\Services\ActivityLogService;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnService $returnService
    ) {}

    public function store(int $loanId, StoreReturn $request): JsonResponse
    {
        $loan = $this->returnService->createByUser(
            $loanId,
            $request->validated()
        );
        app(ActivityLogService::class)->log(
            'return.created',
            'returns',
            "Mengajukan pengembalian untuk loan #{$loan->id}.",
            [
                'loan_id' => $loan->id,
                'return_id' => $loan->toolReturn?->id,
            ]
        );

        return response()->json([
            'message' => 'Pengembalian berhasil dikirim',
            'data' => new ReturnResource($loan->toolReturn),
        ], 201);
    }

    public function confirm(int $loanId, ReviewReturn $request): JsonResponse
    {
        $loan = $this->returnService->confirmByEmployee(
            $loanId,
            $request->user()->id,
            $request->validated()
        );
        app(ActivityLogService::class)->log(
            'return.confirmed',
            'returns',
            "Memverifikasi pengembalian untuk loan #{$loan->id}.",
            [
                'loan_id' => $loan->id,
                'return_id' => $loan->toolReturn?->id,
            ]
        );

        if ($loan->violation) {
            app(ActivityLogService::class)->log(
                'violation.created',
                'violations',
                "Membuat violation #{$loan->violation->id} untuk loan #{$loan->id}.",
                [
                    'violation_id' => $loan->violation->id,
                    'loan_id' => $loan->id,
                    'type' => $loan->violation->type,
                ]
            );
        }

        return response()->json([
            'message' => 'Pengembalian berhasil diverifikasi',
            'data' => new ReturnResource($loan->toolReturn),
        ]);
    }

    public function index(Request $request)
    {
        $returns = $this->returnService->getAll($request->all());

        return ReturnResource::collection($returns);
    }

    public function show(int $id): JsonResponse
    {
        $return = $this->returnService->getById($id);

        return response()->json([
            'data' => new ReturnResource($return)
        ]);
    }
}
