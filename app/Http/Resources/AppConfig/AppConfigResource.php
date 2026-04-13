<?php

namespace App\Http\Resources\AppConfig;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'late_point'     => $this->late_point,
            'broken_point'   => $this->broken_point,
            'lost_point'     => $this->lost_point,
            'late_fine'      => $this->late_fine,
            'broken_fine'    => $this->broken_fine,
            'lost_fine'      => $this->lost_fine,
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
