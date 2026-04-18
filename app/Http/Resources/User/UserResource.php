<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id'            => $this->id,
            'email'         => $this->email,
            'role'          => $this->role,
            'credit_score'  => $this->credit_score,
            'is_restricted' => (bool) $this->is_restricted,
            'created_at'    => $this->created_at?->toIso8601String(),
            
            'details' => $this->detail ? [
                'nik'           => $this->detail->nik,
                'name'          => $this->detail->name,
                'no_hp'         => $this->detail->no_hp,
                'address'       => $this->detail->address,
                'birth_date'    => $this->detail->birth_date?->toIso8601String(),
            ] : null,
        ];
    }
}
