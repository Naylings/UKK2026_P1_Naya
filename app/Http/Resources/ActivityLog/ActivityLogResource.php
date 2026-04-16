<?php

namespace App\Http\Resources\ActivityLog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'module' => $this->module,
            'description' => $this->description,
            'meta' => $this->meta,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'role' => $this->user->role,
                'details' => $this->user->detail ? [
                    'name' => $this->user->detail->name,
                ] : null,
            ] : null),
        ];
    }
}
