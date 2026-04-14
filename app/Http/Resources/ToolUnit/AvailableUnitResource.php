<?php

namespace App\Http\Resources\ToolUnit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailableUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'tool_id' => $this->tool_id,
            'status' => $this->status,
            'notes' => $this->notes,

            'latest_condition' => $this->whenLoaded('latestCondition', function () {
                return $this->latestCondition ? [
                    'conditions' => $this->latestCondition->conditions,
                    'notes' => $this->latestCondition->notes,
                    'recorded_at' => $this->latestCondition->recorded_at?->toIso8601String(),
                ] : null;
            }),

            'availability_reason' => $this->availability_reason,
        ];
    }
}