<?php

namespace App\Http\Resources\Settlement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettlementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'settled_at' => $this->settled_at?->toIso8601String(),
            'description' => $this->description,

            
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'email' => $this->employee->email,
                'role' => $this->employee->role,
                'details' => $this->employee->detail ? [
                    'name' => $this->employee->detail->name,
                ] : null,
            ]),

            
            'violation' => $this->whenLoaded('violation', fn() => [
                'id' => $this->violation->id,
                'type' => $this->violation->type,
                'fine' => $this->violation->fine,
                'status' => $this->violation->status,

                'user' => $this->violation->user ? [
                    'id' => $this->violation->user->id,
                    'details' => $this->violation->user->detail ? [
                        'name' => $this->violation->user->detail->name,
                    ] : null,
                ] : null,
            ]),
        ];
    }
}