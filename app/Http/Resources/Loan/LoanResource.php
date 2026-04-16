<?php

namespace App\Http\Resources\Loan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'loan_date' => $this->loan_date,
            'due_date' => $this->due_date,
            'purpose' => $this->purpose,

            'created_at' => $this->created_at,

            'user' => [
                'id' => $this->whenLoaded('user') ? $this->user->id : null,
                'details' => $this->whenLoaded('user') && $this->user->detail ? [
                    'nik'           => $this->user->detail->nik,
                    'name'          => $this->user->detail->name,
                    'no_hp'         => $this->user->detail->no_hp,
                    'address'       => $this->user->detail->address,
                    'birth_date'    => $this->user->detail->birth_date?->toIso8601String(),
                ] : null,
            ],

            'tool' => [
                'id' => $this->tool_id,
                'name' => $this->whenLoaded('tool') ? $this->tool->name : null,
            ],

            'unit' => [
                'code' => $this->unit_code,
                'status' => $this->whenLoaded('unit', fn() => $this->unit->status),
            ],
            'review' => [
                'employee_id' => $this->employee_id,
                'notes' => $this->notes,

                'employee' => $this->whenLoaded('employee', fn() => [
                    'id' => $this->employee->id,
                    'email' => $this->employee->email,
                    'role' => $this->employee->role,
                    'details' => $this->whenLoaded('employee') && $this->employee->detail ? [
                        'nik'           => $this->employee->detail->nik,
                        'name'          => $this->employee->detail->name,
                        'no_hp'         => $this->employee->detail->no_hp,
                        'address'       => $this->employee->detail->address,
                        'birth_date'    => $this->employee->detail->birth_date?->toIso8601String(),
                    ] : null,
                ]),
            ],

            'tool_return' => $this->whenLoaded('toolReturn', fn() => [
                'id' => $this->toolReturn->id,
                'return_date' => $this->toolReturn->return_date,
                'proof' => $this->toolReturn->proof,
                'notes' => $this->toolReturn->notes,
                'employee' => $this->whenLoaded('toolReturn.employee', fn() => [
                    'id' => $this->toolReturn->employee->id,
                    'email' => $this->toolReturn->employee->email,
                    'details' => $this->toolReturn->employee->detail ? [
                        'name' => $this->toolReturn->employee->detail->name,
                        'no_hp' => $this->toolReturn->employee->detail->no_hp,
                    ] : null,
                ]),
            ]),

            'violation' => $this->whenLoaded('violation', fn() => [
                'id' => $this->violation->id,
                'type' => $this->violation->type,
                'total_score' => $this->violation->total_score,
                'fine' => $this->violation->fine,
                'description' => $this->violation->description,
                'status' => $this->violation->status,
                'settlement' => $this->violation->settlement ? [
                    'id' => $this->violation->settlement->id,
                    'amount' => $this->violation->settlement->amount,
                    'settled_at' => $this->violation->settlement->settled_at,
                    'notes' => $this->violation->settlement->notes,
                    'employee' => $this->violation->settlement->employee ? [
                        'name' => $this->violation->settlement->employee->detail?->name,
                    ] : null,
                ] : null,
            ]),
        ];
    }
}
