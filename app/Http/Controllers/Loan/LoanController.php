<?php

namespace App\Http\Controllers\Loan;


use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Loan\StoreLoanRequest;
use App\Http\Resources\Loan\LoanResource;
use App\Exceptions\LoanException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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

    public function index()
    {
        $loans = $this->loanService->getAll(request()->all());

        return LoanResource::collection($loans);
    }

    public function userLoans()
    {
        $loans = $this->loanService->getByUserId(Auth::id(), request()->all());

        return LoanResource::collection($loans);
    }
}
