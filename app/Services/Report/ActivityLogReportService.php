<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Exceptions\ReportException;
use App\Models\ActivityLog;

class ActivityLogReportService extends BaseReportService
{
    protected function query(ReportFilter $filter)
{
    return ActivityLog::with('user')
        ->when($filter->search, function ($q) use ($filter) {
            $search = "%{$filter->search}%";

            $q->where(function ($query) use ($search) {
                $query->where('description', 'like', $search)
                    ->orWhere('action', 'like', $search)
                    ->orWhere('module', 'like', $search)
                    ->orWhere('meta', 'like', $search)
                    ->orWhereHas('user', function ($qUser) use ($search) {
                        $qUser->where('email', 'like', $search);
                    });
            });
        });
}

    protected function mapPreview($data)
    {
        return $data->map(fn($log) => [
            'user'        => $log->user?->email ?? 'SYSTEM',
            'action'      => $log->action,
            'module'      => $log->module,
            'description' => $log->description,
            'created_at'  => $log->created_at,
        ]);
    }
}
