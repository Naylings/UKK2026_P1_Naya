<?php

namespace App\Http\Controllers\Return;

use App\Http\Controllers\Controller;
use App\Http\Requests\Return\StoreReturn;
use App\Http\Requests\Return\ReviewReturn;
use App\Http\Resources\Return\ReturnResource;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnService $returnService
    ) {}

    // =========================================
    // USER: CREATE RETURN
    // =========================================
    public function store(int $loanId, StoreReturn $request): JsonResponse
    {
        $loan = $this->returnService->createByUser(
            $loanId,
            $request->validated()
        );

        return response()->json([
            'message' => 'Pengembalian berhasil dikirim',
            'data' => new ReturnResource($loan->toolReturn),
        ], 201);
    }

    // =========================================
    // EMPLOYEE: CONFIRM RETURN
    // =========================================
    public function confirm(int $loanId, ReviewReturn $request): JsonResponse
    {
        $loan = $this->returnService->confirmByEmployee(
            $loanId,
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Pengembalian berhasil diverifikasi',
            'data' => new ReturnResource($loan->toolReturn),
        ]);
    }

    // =========================================
    // LIST RETURNS + FILTER
    // =========================================
    public function index(Request $request)
    {
        $returns = $this->returnService->getAll($request->all());

        return ReturnResource::collection($returns);
    }

    // =========================================
    // GET RETURN BY ID
    // =========================================
    public function show(int $id): JsonResponse
    {
        $return = $this->returnService->getById($id);

        return response()->json([
            'data' => new ReturnResource($return)
        ]);
    }
}