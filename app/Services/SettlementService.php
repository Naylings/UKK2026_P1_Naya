<?php

namespace App\Services;

use App\Models\Settlement;
use App\Models\Violation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Exceptions\SettlementException;

class SettlementService
{
    
     public function settle(int $violationId, int $employeeId, string $description): Settlement
     {
         return DB::transaction(function () use ($violationId, $employeeId, $description) {

             $violation = Violation::with('user')->lockForUpdate()->find($violationId);

             if (!$violation) {
                 throw SettlementException::notFound();
             }

             if ($violation->status === 'settled') {
                 throw SettlementException::alreadySettled();
             }

             
             $settlement = Settlement::create([
                 'violation_id' => $violation->id,
                 'employee_id'  => $employeeId,
                 'description'  => $description,
                 'settled_at'   => now(),
             ]);

             
             $violation->update([
                 'status' => 'settled',
             ]);

             
             $violation->user->update([
                 'is_restricted' => 0,
             ]);

             return $settlement->load([
                 'employee.detail',
                 'violation'
             ]);
         });
     }


    
    public function getAll(array $filters = [])
    {
        return Settlement::query()
            ->with([
                'employee.detail',
                'violation.user.detail'
            ])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->whereHas('violation.user.detail', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            })
            ->latest('settled_at')
            ->paginate($filters['per_page'] ?? 10);
    }

    
    public function getById(int $id): Settlement
    {
        $settlement = Settlement::with([
            'employee.detail',
            'violation.user.detail'
        ])->find($id);

        if (!$settlement) {
            throw SettlementException::notFound();
        }

        return $settlement;
    }
}
