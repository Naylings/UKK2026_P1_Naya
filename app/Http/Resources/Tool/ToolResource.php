<?php

namespace App\Http\Resources\Tool;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'category_id'       => $this->category_id,
            'category'          => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'name'              => $this->name,
            'item_type'         => $this->item_type,
            'price'             => $this->price,
            'min_credit_score'  => $this->min_credit_score,
            'description'       => $this->description,
            'code_slug'         => $this->code_slug,
            'photo_path'        => $this->photo_path,
            'bundle_components' => $this->whenLoaded('bundleComponents', fn() => $this->bundleComponents->map(
                fn($component) => [
                    'id' => $component->id,
                    'tool_id' => $component->tool_id,
                    'qty' => $component->qty,
                    'tool' => $component->relationLoaded('tool') && $component->tool
                        ? [
                            'id' => $component->tool->id,
                            'name' => $component->tool->name,
                            'code_slug' => $component->tool->code_slug,
                            'price' => $component->tool->price,
                            'item_type' => $component->tool->item_type,
                        ]
                        : null,
                ]
            )->values()),
            'created_at'        => $this->created_at?->toIso8601String(),
            // ─────────────────────────────────────────────────────────────────
            // Metadata untuk frontend decision making
            // ─────────────────────────────────────────────────────────────────
            'has_units'         => $this->units()->exists(),
            'units_count'       => $this->units()->count(),
            'available_units'   => $this->units()->where('status', 'available')->count(),
            'borrowed_units'    => $this->units()->where('status', 'lent')->count(),
            'nonactive_units'   => $this->units()->where('status', 'nonactive')->count(),
            'has_loans'         => $this->loans()->exists(),
            'has_bundles'       => $this->bundles()->exists(),
            'can_delete'        => !$this->units()->exists()
                                   && !$this->loans()->exists()
                                   && !$this->bundles()->exists(),
        ];
    }
}
