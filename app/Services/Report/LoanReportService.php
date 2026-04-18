<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\Loan;
use Carbon\Carbon;
use App\Exceptions\ReportException;

class LoanReportService extends BaseReportService
{
    private int $maxDays = 90;

    protected function query(ReportFilter $filter)
    {
        $q = Loan::with(['user.appeals', 'tool']);
        $q->when($filter->startDate, fn($query) =>
                $query->whereDate('loan_date', '>=', $filter->startDate)
            )->when($filter->endDate, fn($query) =>
                $query->whereDate('loan_date', '<=', $filter->endDate)
            )->when($filter->userId, fn($query) =>
                $query->where('user_id', $filter->userId)
            )->when($filter->toolId, fn($query) =>
                $query->where('tool_id', $filter->toolId)
            )->when($filter->status, fn($query) =>
                $query->where('status', $filter->status)
            )->when($filter->toolName, fn($query) =>
                $query->whereHas('tool', fn($t) => $t->where('name', 'like', '%' . $filter->toolName . '%'))
            )->when($filter->search, fn($query) =>
                $query->where(function ($sub) use ($filter) {
                    $sub->where('status', 'like', '%' . $filter->search . '%')
                        ->orWhereHas('user', function ($u) use ($filter) {
                            $u->where('email', 'like', '%' . $filter->search . '%')
                              ->orWhereHas('detail', fn ($ud) => $ud->where('name', 'like', '%' . $filter->search . '%'));
                        })
                        ->orWhereHas('tool', fn ($t) => $t->where('name', 'like', '%' . $filter->search . '%'));
                })
            );
        return $q;
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
        
            $data = $this->query($filter)->get();
        
            if ($data->isEmpty()) {
                throw ReportException::noData();
            }
        
            return $this->mapPreview($data);
    }

    protected function mapPreview($data)
    {
        return $data->map(fn($loan) => [
            'id'         => $loan->id,
            'user'       => $loan->user?->email,
            'tool'       => $loan->tool?->name,
            'status'     => $loan->status,
            'loan_date'  => $loan->loan_date,
            'due_date'   => $loan->due_date,
        ]);
    }
}