<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\Violation;
use Carbon\Carbon;
use App\Exceptions\ReportException;

class ViolationReportService extends BaseReportService
{
    private int $maxDays = 90;

    protected function query(ReportFilter $filter)
    {
        return Violation::with(['user', 'loan.tool'])
            ->when($filter->startDate, fn($q) =>
                $q->whereDate('created_at', '>=', $filter->startDate)
            )
            ->when($filter->endDate, fn($q) =>
                $q->whereDate('created_at', '<=', $filter->endDate)
            )->when($filter->search, fn($q) =>
                $q->where(function ($sub) use ($filter) {
                    $sub->where('type', 'like', '%' . $filter->search . '%')
                        ->orWhere('status', 'like', '%' . $filter->search . '%')
                        ->orWhere('description', 'like', '%' . $filter->search . '%')
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%' . $filter->search . '%')->orWhereHas('detail', fn ($ud) => $ud->where('name', 'like', '%' . $filter->search . '%')))
                        ->orWhereHas('loan.tool', fn ($t) => $t->where('name', 'like', '%' . $filter->search . '%'));
                })
            );
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
        return $data->map(fn($v) => [
            'id'        => $v->id,
            'user'      => $v->user?->email,
            'tool'      => $v->loan?->tool?->name,
            'type'      => $v->type,
            'fine'      => $v->fine,
            'status'    => $v->status,
            'created_at'=> $v->created_at,
        ]);
    }
}
