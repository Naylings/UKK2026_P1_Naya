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
        ];
    }
}
