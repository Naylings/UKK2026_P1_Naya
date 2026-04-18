<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'];

        return [
            'access_token' => $this->resource['token'],
            'token_type'   => $this->resource['token_type'],
            'expires_in'   => $this->resource['expires_in'],

            'user' => [
                'id'            => $user->id,
                'email'         => $user->email,
                'role'          => $user->role,
                'credit_score'  => $user->credit_score,
                'is_restricted' => (bool) $user->is_restricted,
                'name'          => $user->detail?->name,    
                'created_at'    => $user->created_at?->toIso8601String(),
            ],
        ];
    }
}
