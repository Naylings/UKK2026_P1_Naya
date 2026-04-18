<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\ToolReturn;
use Carbon\Carbon;
use App\Exceptions\ReportException;

class ReturnReportService extends BaseReportService
{
    private int $maxDays = 90;

    protected function query(ReportFilter $filter)
    {
        return ToolReturn::with(['loan.user', 'loan.tool', 'employee', 'conditions'])
            ->when($filter->userId, fn($q) => $q->whereHas('loan', fn($l) => $l->where('user_id', $filter->userId)))
            ->when($filter->toolId, fn($q) => $q->whereHas('loan', fn($l) => $l->where('tool_id', $filter->toolId)))
            ->when($filter->status, fn($q) => $q->whereHas('loan', fn($l) => $l->where('status', $filter->status)))
            ->when($filter->toolName, fn($q) => $q->whereHas('loan.tool', fn($t) => $t->where('name', 'like', '%' . $filter->toolName . '%')))
            ->when($filter->unitCode, fn($q) => $q->whereHas('loan', fn($l) => $l->where('unit_code', $filter->unitCode)))
            ->when(
                $filter->startDate,
                fn($q) =>
                $q->whereDate('return_date', '>=', $filter->startDate)
            )
            ->when(
                $filter->endDate,
                fn($q) =>
                $q->whereDate('return_date', '<=', $filter->endDate)
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
            });
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
        return $data->map(function ($r) {
            $latestCondition = $r->conditions
                ? $r->conditions->sortByDesc('recorded_at')->first()
                : null;

            return [
                'return_date'   => $r->return_date,
                'user'          => $r->loan?->user?->email,
                'tool'          => $r->loan?->tool?->name,
                'unit_code'     => $r->loan?->unit_code,
                'loan_status'   => $r->loan?->status,

                'review_status' => $r->reviewed ? 'Sudah Direview' : 'Belum Direview',

                'condition'     => $latestCondition?->conditions ?? 'Tidak Ada Data',

                'employee'      => $r->employee?->email,
            ];
        });
    }
}
