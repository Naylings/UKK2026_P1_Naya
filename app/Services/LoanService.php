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
            $hasActiveLoan = Loan::where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'active'])
                ->exists();

            if ($hasActiveLoan) {
                throw LoanException::userHasActiveLoan();
            }

            $unit = ToolUnit::where('code', $data['unit_code'])->first();

            if (!$unit) {
                throw LoanException::unitNotFound();
            }

            $conflict = Loan::where('unit_code', $data['unit_code'])
                ->whereIn('status', ['pending', 'active'])
                ->where(function ($q) use ($data) {
                    $q->whereBetween('loan_date', [$data['loan_date'], $data['due_date']])
                        ->orWhereBetween('due_date', [$data['loan_date'], $data['due_date']]);
                })
                ->exists();

            if ($conflict) {
                throw LoanException::unitNotAvailable();
            }

            $loan = Loan::create([
                'user_id' => Auth::id(),
                'tool_id' => $data['tool_id'],
                'unit_code' => $data['unit_code'],
                'status' => 'pending',
                'loan_date' => $data['loan_date'],
                'due_date' => $data['due_date'],
                'purpose' => $data['purpose'],
            ]);

            return $loan;
        });
    }


    public function getAll(array $filters = [])
    {
        return Loan::query()
            ->with(['user', 'tool', 'unit'])
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('purpose', 'like', "%{$search}%")
                        ->orWhere('unit_code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }


    public function getByUserId(int $userId, array $filters = [])
    {
        return Loan::query()
            ->with(['tool', 'unit'])
            ->where('user_id', $userId)
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }
}
