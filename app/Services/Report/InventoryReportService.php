<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\Tool;

class InventoryReportService extends BaseReportService
{
    protected function query(ReportFilter $filter)
{
    return Tool::query()
        ->with(['category', 'bundleComponents.tool'])
        ->withCount([
            'units as total_unit',
            'units as available' => fn($q) => $q->where('status', 'available'),
            'units as lent' => fn($q) => $q->where('status', 'lent'),
            'units as nonactive' => fn($q) => $q->where('status', 'nonactive'),
        ])
        ->when($filter->search, function ($q) use ($filter) {
            $search = $filter->search;

            $q->where(function ($q) use ($search) {
                $q->where('tools.name', 'like', "%{$search}%")
                  ->orWhere('tools.code_slug', 'like', "%{$search}%")
                  ->orWhere('tools.item_type', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        });
}

    protected function mapPreview($data)
    {
        return $data->map(function ($tool) {
            $bundleInfo = '';
            if (isset($tool->item_type) && $tool->item_type === 'bundle' && $tool->bundleComponents && $tool->bundleComponents->isNotEmpty()) {
                $bundleInfo = $tool->bundleComponents->map(fn($bt) => $bt->tool->name . ' x ' . $bt->qty)->implode(', ');
            }
            return [
                'tool'        => $tool->name,
                'item_type'   => $tool->item_type ?? 'N/A',
                'category'    => $tool->category?->name ?? 'N/A',
                'total_unit'  => $tool->units->count(),
                'available'   => $tool->units->where('status', 'available')->count(),
                'lent'        => $tool->units->where('status', 'lent')->count(),
                'nonactive'   => $tool->units->where('status', 'nonactive')->count(),
                'bundle_tools' => $bundleInfo ?: 'N/A',
            ];
        });
    }

    public function export(array $filters)
    {
        return $this->preview($filters);
    }
}
