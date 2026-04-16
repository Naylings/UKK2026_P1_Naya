<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\ToolUnit;
use App\Exceptions\LoanException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanService
{

    public function create(array $data): Loan
    {
        return DB::transaction(function () use ($data) {

            $user = Auth::user();

            if ($user->is_restricted) {
                throw LoanException::createFailed('Anda masih memiliki pinjaman aktif.');
            }

            $tool = \App\Models\Tool::find($data['tool_id']);

            if ($tool && $tool->min_credit_score !== null) {
                if ($user->credit_score < $tool->min_credit_score) {
                    throw LoanException::createFailed('Credit score tidak mencukupi.');
                }
            }

            $conflict = Loan::where('unit_code', $data['unit_code'])
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($q) use ($data) {
                    $q->where('loan_date', '<=', $data['due_date'])
                      ->where('due_date', '>=', $data['loan_date']);
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw LoanException::unitNotAvailable();
            }

            $loan = Loan::create([
                'user_id' => $user->id,
                'tool_id' => $data['tool_id'],
                'unit_code' => $data['unit_code'],
                'status' => 'pending',
                'loan_date' => $data['loan_date'],
                'due_date' => $data['due_date'],
                'purpose' => $data['purpose'],
            ]);


            $loan->user()->update([
                'is_restricted' => 1
            ]);
            return $loan;
        });
    }


    public function getAll(array $filters = [])
    {
        return Loan::query()
            ->with([
                'user.detail',
                'tool',
                'unit',
                'employee.detail',
                'toolReturn.employee.detail',
                'violation.settlement.employee.detail'
            ])
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('purpose', 'like', "%{$search}%")
                        ->orWhere('unit_code', 'like', "%{$search}%")
                        ->orWhereHas('tool', function ($toolQuery) use ($search) {
                            $toolQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }


    public function getByUserId(int $userId, array $filters = [])
    {
        return Loan::query()
            ->with([
                'user.detail',
                'tool',
                'unit',
                'employee.detail',
                'toolReturn.employee.detail',
                'violation.settlement.employee.detail'
            ])
            ->where('user_id', $userId)
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('purpose', 'like', "%{$search}%")
                        ->orWhere('unit_code', 'like', "%{$search}%")
                        ->orWhereHas('tool', function ($toolQuery) use ($search) {
                            $toolQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }


    public function approve(int $loanId, int $employeeId, ?string $notes = null): Loan
    {
        return DB::transaction(function () use ($loanId, $employeeId, $notes) {

            $loan = Loan::lockForUpdate()->find($loanId);

            if (!$loan) {
                throw LoanException::notFound();
            }

            if ($loan->status !== 'pending') {
                throw LoanException::notPending();
            }

            $hasActiveLoan = Loan::where('unit_code', $loan->unit_code)
                ->where('status', 'approved')
                ->where(function ($q) use ($loan) {
                    $q->where('loan_date', '<=', $loan->due_date)
                      ->where('due_date', '>=', $loan->loan_date);
                })
                ->lockForUpdate()
                ->exists();

            if ($hasActiveLoan) {
                throw LoanException::unitNotAvailable();
            }

            $loan->update([
'status' => 'approved',
                'employee_id' => $employeeId,
                'notes' => $notes,
            ]);

            return $loan->fresh(['user.detail', 'tool', 'unit', 'employee.detail']);
        });
    }


    public function reject(int $loanId, int $employeeId, ?string $notes = null): Loan
    {
        return DB::transaction(function () use ($loanId, $employeeId, $notes) {

            $loan = Loan::lockForUpdate()->find($loanId);

            if (!$loan) {
                throw LoanException::notFound();
            }

            if ($loan->status !== 'pending') {
                throw LoanException::notPending();
            }

            $loan->update([
                'status' => 'rejected',
                'employee_id' => $employeeId,
                'notes' => $notes,
            ]);

            $loan->user->update([
                'is_restricted' => 0
            ]);

            return $loan->fresh(['user.detail', 'tool', 'unit', 'employee.detail']);
        });
    }
}
