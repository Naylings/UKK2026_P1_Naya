<?php

namespace App\Http\Resources\Violation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'total_score' => $this->total_score,
            'fine' => $this->fine,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),

            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'details' => $this->user->detail ? [
                    'name' => $this->user->detail->name,
                ] : null,
            ]),

            'loan' => $this->whenLoaded('loan', fn() => [
                'id' => $this->loan->id,
                'loan_date' => $this->loan->loan_date,
                'due_date' => $this->loan->due_date,
                'purpose' => $this->loan->purpose,
                'tool' => $this->loan->tool ? [
                    'id' => $this->loan->tool->id,
                    'name' => $this->loan->tool->name,
                    'price' => $this->loan->tool->price,
                    'item_type' => $this->loan->tool->item_type,
                    'code_slug' => $this->loan->tool->code_slug,
                ] : null,
                'unit' => $this->loan->unit ? [
                    'code' => $this->loan->unit->code,
                    'status' => $this->loan->unit->status,
                ] : null,
            ]),

            'tool_return' => $this->whenLoaded('toolReturn', fn() => [
                'id' => $this->toolReturn->id,
                'return_date' => $this->toolReturn->return_date?->toIso8601String(),
                'created_at' => $this->toolReturn->created_at?->toIso8601String(),
                'employee' => $this->toolReturn->employee ? [
                    'id' => $this->toolReturn->employee->id,
                    'email' => $this->toolReturn->employee->email,
                    'details' => $this->toolReturn->employee->detail ? [
                        'name' => $this->toolReturn->employee->detail->name,
                    ] : null,
                ] : null,
            ]),

            'settlement' => $this->whenLoaded('settlement', fn() => $this->settlement ? [
                'id' => $this->settlement->id,
                'description' => $this->settlement->description,
                'settled_at' => $this->settlement->settled_at?->toIso8601String(),
                'employee' => $this->settlement->employee ? [
                    'id' => $this->settlement->employee->id,
                    'email' => $this->settlement->employee->email,
                    'details' => $this->settlement->employee->detail ? [
                        'name' => $this->settlement->employee->detail->name,
                    ] : null,
                ] : null,
            ] : null),
        ];
    }
}
