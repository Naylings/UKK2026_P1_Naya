<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource ini menerima array hasil dari AuthService,
 * bukan langsung Model, sehingga kita akses via $this->resource[key].
 *
 * Shape input:
 * [
 *   'token'      => string|null,
 *   'token_type' => string|null,
 *   'expires_in' => int|null,
 *   'user'       => User,
 * ]
 */
class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'];

        return [
            // Token section — null saat endpoint /me
            'access_token' => $this->resource['token'],
            'token_type'   => $this->resource['token_type'],
            'expires_in'   => $this->resource['expires_in'],

            // User section
            'user' => [
                'id'            => $user->id,
                'email'         => $user->email,
                'role'          => $user->role,
                'credit_score'  => $user->credit_score,
                'is_restricted' => (bool) $user->is_restricted,
                'name'          => $user->detail?->name,    // dari relasi user_details
                'created_at'    => $user->created_at?->toIso8601String(),
            ],
        ];
    }
}
