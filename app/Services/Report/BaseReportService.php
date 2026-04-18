<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Exceptions\ReportException;
use Carbon\Carbon;

abstract class BaseReportService
{
    
    protected string $dateFormat = 'Y-m-d';

    abstract protected function query(ReportFilter $filter);

    protected function mapPreview($data)
    {
        return $data;
    }

    
    protected function formatDates($mapped, bool $asArray = false)
    {
        $collection = $mapped instanceof \Illuminate\Support\Collection
            ? $mapped
            : collect($mapped);

        $formatted = $collection->map(function ($row) {
            
            $arr = is_array($row) ? $row : (array) $row;

            foreach ($arr as $k => $v) {
                if ($v instanceof \DateTimeInterface) {
                    $arr[$k] = Carbon::instance($v)->format($this->dateFormat);
                    continue;
                }

                
                if (is_string($v) && strtotime($v) !== false) {
                    try {
                        $arr[$k] = Carbon::parse($v)->format($this->dateFormat);
                    } catch (\Throwable $e) {
                        
                    }
                }
            }

            return $arr;
        });

        return $asArray ? $formatted->values()->toArray() : $formatted;
    }

    public function preview(array $filters)
    {
        $filter = ReportFilter::fromArray($filters);

        $data = $this->query($filter)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        if ($data->isEmpty()) {
            throw ReportException::noData();
        }

        $mapped = $this->mapPreview($data);

        return $this->formatDates($mapped, false);
    }

    public function export(array $filters)
    {
        $filter = ReportFilter::fromArray($filters);

        $data = $this->query($filter)->get();

        if ($data->isEmpty()) {
            throw ReportException::noData();
        }

        $mapped = $this->mapPreview($data);

        
        return $this->formatDates($mapped, true);
    }
}
