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

            'created_at' => $this->created_at,

            // =========================================
            // EMPLOYEE (PETUGAS YANG VALIDASI RETURN)
            // =========================================
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'email' => $this->employee->email,
                'role' => $this->employee->role,
                'details' => $this->employee->detail ? [
                    'name'       => $this->employee->detail->name,
                    'no_hp'      => $this->employee->detail->no_hp,
                ] : null,
            ]),

            // =========================================
            // LOAN INFO (CONTEXT PENGEMBALIAN)
            // =========================================
            'loan' => $this->whenLoaded('loan', fn() => [
                'id' => $this->loan->id,
                'status' => $this->loan->status,
                'loan_date' => $this->loan->loan_date,
                'due_date' => $this->loan->due_date,
                'purpose' => $this->loan->purpose,
                'created_at' => $this->loan->created_at,

                'user' => [
                    'id' => $this->loan->user_id,
                    'details' => $this->loan->user->detail ? [
                        'name' => $this->loan->user->detail->name,
                        'email' => $this->loan->user->email,
                        'no_hp' => $this->loan->user->detail->no_hp,
                        'nik' => $this->loan->user->detail->nik,
                        'address' => $this->loan->user->detail->address,
                    ] : null,
                ],

                'tool' => [
                    'id' => $this->loan->tool_id,
                    'name' => $this->loan->tool?->name,
                    'price' => $this->loan->tool?->price,
                    'item_type' => $this->loan->tool?->item_type,
                    'code_slug' => $this->loan->tool?->code_slug,
                    'bundle_components' => $this->loan->tool && $this->loan->tool->item_type === 'bundle'
                        ? $this->loan->tool->bundleComponents->map(fn($bc) => [
                            'name' => $bc->tool?->name,
                            'qty' => $bc->qty,
                            'code' => $bc->tool?->code_slug,
                            'price' => $bc->tool?->price, // 🔥 Added price for calculation
                        ]) : null,                ],

                'unit' => [
                    'code' => $this->loan->unit_code,
                    'status' => $this->loan->unit?->status,
                ],

                'review' => [
                    'employee' => $this->loan->employee ? [
                        'details' => [
                            'name' => $this->loan->employee->detail?->name,
                        ],
                        'email' => $this->loan->employee->email,
                    ] : null,
                ],

                'tool_return' => [
                    'id' => $this->id,
                    'return_date' => $this->return_date,
                    'proof' => $this->proof,
                    'employee' => $this->employee ? [
                        'details' => [
                            'name' => $this->employee->detail?->name,
                        ]
                    ] : null,
                ],

                'violation' => $this->violation ? [
                    'id' => $this->violation->id,
                    'type' => $this->violation->type,
                    'total_score' => $this->violation->total_score,
                    'fine' => $this->violation->fine,
                    'description' => $this->violation->description,
                    'status' => $this->violation->status,
                ] : null,

                'conditions' => $this->whenLoaded('conditions', function () {
                    return $this->conditions->map(fn($c) => [
                        'id' => $c->id,
                        'unit_code' => $c->unit_code,
                        'conditions' => $c->conditions,
                        'notes' => $c->notes,
                        'recorded_at' => $c->recorded_at,
                    ]);
                }),
            ]),
        ];
    }
}
