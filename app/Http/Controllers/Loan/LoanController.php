<?php

namespace App\Http\Controllers\Loan;

use App\Services\LoanService;
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

    // 🔥 REVIEW APPROVE
    public function approve(int $loanId, Request $request): JsonResponse
    {
        $loan = $this->loanService->approve(
            $loanId,
            $request->user()->id,
            $request->input('notes')
        );

        return response()->json([
            'message' => 'Peminjaman disetujui',
            'data' => new LoanResource($loan),
        ]);
    }

    // 🔥 REVIEW REJECT
    public function reject(int $loanId, Request $request): JsonResponse
    {
        $loan = $this->loanService->reject(
            $loanId,
            $request->user()->id,
            $request->input('notes')
        );

        return response()->json([
            'message' => 'Peminjaman ditolak',
            'data' => new LoanResource($loan),
        ]);
    }
}