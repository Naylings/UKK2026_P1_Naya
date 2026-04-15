<?php

namespace App\Http\Resources\Return;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'return_date' => $this->return_date,
            'proof' => $this->proof,
            'notes' => $this->notes,

            'created_at' => $this->created_at,

            // =========================================
            // EMPLOYEE (PETUGAS YANG VALIDASI RETURN)
            // =========================================
            'employee' => [
                'id' => $this->whenLoaded('employee', fn() => $this->employee->id),
                'email' => $this->whenLoaded('employee', fn() => $this->employee->email),
                'role' => $this->whenLoaded('employee', fn() => $this->employee->role),

                'details' => $this->whenLoaded('employee') && $this->employee?->detail ? [
                    'nik'        => $this->employee->detail->nik,
                    'name'       => $this->employee->detail->name,
                    'no_hp'      => $this->employee->detail->no_hp,
                    'address'    => $this->employee->detail->address,
                    'birth_date' => $this->employee->detail->birth_date?->toIso8601String(),
                ] : null,
            ],

            // =========================================
            // CONDITION (HASIL INSPEKSI UNIT)
            // =========================================
            'conditions' => $this->whenLoaded('conditions', function () {
                return $this->conditions->map(fn($c) => [
                    'id' => $c->id,
                    'unit_code' => $c->unit_code,
                    'conditions' => $c->conditions,
                    'notes' => $c->notes,
                    'recorded_at' => $c->recorded_at?->toIso8601String(),
                ]);
            }),

            // =========================================
            // LOAN INFO (CONTEXT PENGEMBALIAN)
            // =========================================
            'loan' => [
                'id' => $this->loan?->id,

                'status' => $this->loan?->status,
                'loan_date' => $this->loan?->loan_date,
                'due_date' => $this->loan?->due_date,

                'tool' => $this->whenLoaded('loan', fn() => [
                    'id' => $this->loan->tool_id,
                    'name' => $this->loan->tool?->name,
                ]),

                'unit' => $this->whenLoaded('loan', fn() => [
                    'code' => $this->loan->unit_code,
                    'status' => $this->loan->unit?->status,
                ]),
            ],

            // =========================================
            // VIOLATION (JIKA ADA MASALAH)
            // =========================================
            'violation' => $this->whenLoaded('loan', function () {

                $violation = $this->violation ?? null;

                if (!$violation) {
                    return null;
                }

                return [
                    'id' => $violation->id,
                    'type' => $violation->type,
                    'fine' => $violation->fine,
                    'total_score' => $violation->total_score,
                    'description' => $violation->description,
                    'status' => $violation->status,
                ];
            }),
        ];
    }
}
