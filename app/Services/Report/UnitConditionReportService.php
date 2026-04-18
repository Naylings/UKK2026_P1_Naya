<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\UnitCondition;
use App\Exceptions\ReportException;

class UnitConditionReportService extends BaseReportService
{
    protected string $dateFormat = 'Y/m/d';

    protected function query(ReportFilter $filter)
{
    return UnitCondition::query()
        ->with(['unit.tool'])

        ->when($filter->startDate, fn($q) =>
            $q->whereDate('recorded_at', '>=', $filter->startDate)
        )
        ->when($filter->endDate, fn($q) =>
            $q->whereDate('recorded_at', '<=', $filter->endDate)
        )

        ->when($filter->search, function ($q) use ($filter) {
            $search = $filter->search;

            $q->where(function ($q) use ($search) {
                $q->where('unit_code', 'like', "%{$search}%")
                  ->orWhere('conditions', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")

                  ->orWhereHas('unit.tool', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        })

        ->orderBy('recorded_at', 'desc');
}

    protected function mapPreview($data)
    {
        return $data->map(function ($c) {
            return [
                'unit_code'   => $c->unit_code,
                'tool'        => $c->unit?->tool?->name,
                'condition'   => $c->conditions,
                'recorded_at' => $c->recorded_at,
                'notes'       => $c->notes,
            ];
        });
    }
}
