<?php

namespace App\Http\Resources\ToolUnit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToolUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code'              => $this->code,
            'tool_id'           => $this->tool_id,
            'tool'              => $this->whenLoaded('tool', fn() => [
                'id'        => $this->tool->id,
                'name'      => $this->tool->name,
                'code_slug' => $this->tool->code_slug,
                'item_type' => $this->tool->item_type,
            ]),
            'status'            => $this->status,
            'notes'             => $this->notes,
            'created_at'        => $this->created_at?->toIso8601String(),

            // ─────────────────────────────────────────────────────────────────
            // Latest condition (kondisi terkini unit)
            // ─────────────────────────────────────────────────────────────────
            'latest_condition'  => $this->whenLoaded('latestCondition', fn() => $this->latestCondition ? [
                'id'           => $this->latestCondition->id,
                'conditions'   => $this->latestCondition->conditions,
                'notes'        => $this->latestCondition->notes,
                'recorded_at'  => $this->latestCondition->recorded_at?->toIso8601String(),
            ] : null),

            // ─────────────────────────────────────────────────────────────────
            // Metadata untuk frontend
            // ─────────────────────────────────────────────────────────────────
            'is_available'      => $this->status === 'available',
            'is_lent'           => $this->status === 'lent',
            'is_nonactive'      => $this->status === 'nonactive',
            'has_loans'         => $this->loans()->exists(),
        ];
    }
}
