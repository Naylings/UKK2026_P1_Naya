<?php

namespace App\Http\Resources\ToolUnit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitConditionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'unit_code'    => $this->unit_code,
            'unit'         => $this->whenLoaded('unit', fn() => [
                'code'    => $this->unit->code,
                'tool_id' => $this->unit->tool_id,
                'status'  => $this->unit->status,
            ]),
            'return_id'    => $this->return_id,
            'conditions'   => $this->conditions,
            'notes'        => $this->notes,
            'recorded_at'  => $this->recorded_at?->toIso8601String(),
        ];
    }
}
