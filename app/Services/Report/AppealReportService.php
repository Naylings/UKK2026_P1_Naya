<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\Appeal;
use Carbon\Carbon;
use App\Exceptions\ReportException;

class AppealReportService extends BaseReportService
{
    private int $maxDays = 90;

    protected function query(ReportFilter $filter)
    {
        return Appeal::with(['user', 'reviewer'])
            ->when($filter->userId, fn($q) => $q->where('user_id', $filter->userId))
            ->when($filter->status, fn($q) => $q->where('status', $filter->status))
            ->when($filter->startDate, fn($q) =>
                $q->whereDate('created_at', '>=', $filter->startDate)
            )
            ->when($filter->endDate, fn($q) =>
                $q->whereDate('reviewed_at', '<=', $filter->endDate)
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
        return $data->map(fn($a) => [
            'id'            => $a->id,
            'user_email'    => $a->user?->email,
            'reviewer_email' => $a->reviewer?->email,
            'status'        => $a->status,
            'credit_changed' => $a->credit_changed,
            'reason'        => substr($a->reason, 0, 100) . '...',
            'created_at'    => $a->created_at,
            'reviewed_at'   => $a->reviewed_at,
        ]);
    }
}

