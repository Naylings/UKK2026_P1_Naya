<?php

namespace App\Http\Resources\Appeal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'status' => $this->status,
            'credit_changed' => $this->credit_changed,
            'admin_notes' => $this->admin_notes,
            'created_at' => $this->created_at,
            'reviewed_at' => $this->reviewed_at,

            'user' => [
                'id' => $this->whenLoaded('user') ? $this->user->id : null,
                'details' => $this->whenLoaded('user') && $this->user->detail ? [
                    'nik' => $this->user->detail->nik,
                    'name' => $this->user->detail->name,
                    'no_hp' => $this->user->detail->no_hp,
                ] : null,
            ],

            'reviewer' => $this->whenLoaded('reviewer', function () {
                return [
                    'id' => $this->reviewer->id,
                    'email' => $this->reviewer->email,
                    'role' => $this->reviewer->role,
                    'details' => $this->reviewer->detail ? [
                        'name' => $this->reviewer->detail->name,
                    ] : null,
                ];
            }),
        ];
    }
}
?>

