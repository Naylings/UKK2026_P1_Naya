<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\Settlement;
use Carbon\Carbon;
use App\Exceptions\ReportException;

class SettlementReportService extends BaseReportService
{
    private int $maxDays = 90;

    protected function query(ReportFilter $filter)
    {
        return Settlement::with([
            'violation.user',
            'violation.loan.tool',
            'employee'
        ])
            ->when(
                $filter->startDate,
                fn($q) =>
                $q->whereDate('settled_at', '>=', $filter->startDate)
            )
            ->when(
                $filter->endDate,
                fn($q) =>
                $q->whereDate('settled_at', '<=', $filter->endDate)
            )->when($filter->search, function ($q) use ($filter) {
                $search = '%' . $filter->search . '%';

                $q->where(function ($query) use ($search) {
                    $query
                        
                        ->whereHas(
                            'violation.user',
                            fn($u) =>
                            $u->where('email', 'like', $search)
                        )

                        
                        ->orWhereHas(
                            'violation.loan.tool',
                            fn($t) =>
                            $t->where('name', 'like', $search)
                        )

                        
                        ->orWhereHas(
                            'violation.loan',
                            fn($l) =>
                            $l->where('unit_code', 'like', $search)
                        )

                        
                        ->orWhereHas(
                            'violation',
                            fn($v) =>
                            $v->where('type', 'like', $search)
                        )

                        
                        ->orWhere('description', 'like', $search);
                });
            })
            ->orderBy('settled_at', 'desc');
    }

    private function validate(ReportFilter $filter): void
    {
        if (!$filter->startDate || !$filter->endDate) {
            throw ReportException::missingDate();
        }

        $diff = Carbon::parse($filter->startDate)
            ->diffInDays($filter->endDate);

        if ($diff > $this->maxDays) {
            throw ReportException::dateRangeTooLarge($this->maxDays);
        }
    }

    public function preview(array $filters)
    {
        $filter = ReportFilter::fromArray($filters);
        $this->validate($filter);

        return parent::preview($filters);
    }

    public function export(array $filters)
    {
        $filter = ReportFilter::fromArray($filters);
        $this->validate($filter);

        return parent::export($filters);
    }



    protected function mapPreview($data)
    {
        return $data->map(function ($s) {
            $violation = $s->violation;
            $loan = $violation?->loan;

            return [
                'settled_at'     => $s->settled_at,

                'user'           => $violation?->user?->email,

                'tool'           => $loan?->tool?->name,
                'unit_code'      => $loan?->unit_code,

                'violation_type' => $this->mapViolationType($violation?->type),

                'fine'           => $violation?->fine,
                'total_score'    => $violation?->total_score,

                'settlement'     => $s->description,

                'employee'       => $s->employee?->email,

                'status'         => $this->mapStatus($violation?->status),
            ];
        });
    }
    private function mapViolationType(?string $type): string
    {
        return match ($type) {
            'late' => 'Terlambat',
            'damaged' => 'Rusak',
            'lost' => 'Hilang',
            'other' => 'Lainnya',
            default => '-',
        };
    }

    private function mapStatus(?string $status): string
    {
        return match ($status) {
            'active' => 'Belum Lunas',
            'settled' => 'Lunas',
            default => '-',
        };
    }
}
