<?php

namespace App\Services;

use App\Exceptions\SettlementException;
use App\Models\Violation;

class ViolationService
{
    public function getAll(array $filters = [])
    {
        return Violation::query()
            ->with([
                'user.detail',
                'loan.tool',
                'loan.unit',
                'toolReturn.employee.detail',
                'settlement.employee.detail',
            ])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->whereHas('user.detail', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    })->orWhereHas('loan.tool', function ($toolQuery) use ($search) {
                        $toolQuery->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['type'] ?? null, function ($q, $type) {
                $q->where('type', $type);
            })
            ->latest('created_at')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getById(int $id): Violation
    {
        $violation = Violation::with([
            'user.detail',
            'loan.tool',
            'loan.unit',
            'toolReturn.employee.detail',
            'settlement.employee.detail',
        ])->find($id);

        if (!$violation) {
            throw SettlementException::notFound();
        }

        return $violation;
    }
}
