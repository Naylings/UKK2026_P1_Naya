<?php

namespace App\Http\Controllers\Loan;

use App\Services\LoanService;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Loan\StoreLoanRequest;
use App\Http\Resources\Loan\LoanResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ) {}

    public function store(StoreLoanRequest $request): JsonResponse
    {
        $loan = $this->loanService->create($request->validated());
        app(ActivityLogService::class)->log(
            'loan.created',
            'loans',
            "Membuat pengajuan peminjaman #{$loan->id}.",
            [
                'loan_id' => $loan->id,
                'tool_id' => $loan->tool_id,
                'unit_code' => $loan->unit_code,
            ]
        );

        return response()->json([
            'message' => 'Pengajuan peminjaman berhasil',
            'data' => new LoanResource($loan),
        ], 201);
    }

    public function index(Request $request)
    {
        $loans = $this->loanService->getAll($request->all());

        return LoanResource::collection($loans);
    }

    public function userLoans(Request $request)
    {
        $loans = $this->loanService->getByUserId(
            $request->user()->id,
            $request->all()
        );

        return LoanResource::collection($loans);
    }

    public function approve(int $loanId, Request $request): JsonResponse
    {
        $loan = $this->loanService->approve(
            $loanId,
            $request->user()->id,
            $request->input('notes')
        );
        app(ActivityLogService::class)->log(
            'loan.approved',
            'loans',
            "Menyetujui peminjaman #{$loan->id}.",
            ['loan_id' => $loan->id, 'notes' => $loan->notes]
        );

        return response()->json([
            'message' => 'Peminjaman disetujui',
            'data' => new LoanResource($loan),
        ]);
    }

    public function reject(int $loanId, Request $request): JsonResponse
    {
        $loan = $this->loanService->reject(
            $loanId,
            $request->user()->id,
            $request->input('notes')
        );
        app(ActivityLogService::class)->log(
            'loan.rejected',
            'loans',
            "Menolak peminjaman #{$loan->id}.",
            ['loan_id' => $loan->id, 'notes' => $loan->notes]
        );

        return response()->json([
            'message' => 'Peminjaman ditolak',
            'data' => new LoanResource($loan),
        ]);
    }
}
