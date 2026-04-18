<?php

namespace App\Services\Report;

use App\DTO\ReportFilter;
use App\Models\User;

class UserReportService extends BaseReportService
{
    protected function query(ReportFilter $filter)
{
    return User::with('detail')
        ->withCount('activityLogs')
        ->with(['activityLogs' => fn($q) => $q->latest()->limit(3)])
        ->when($filter->search, function ($q) use ($filter) {
            $search = "%{$filter->search}%";

            $q->where(function ($query) use ($search) {
                $query->where('email', 'like', $search)
                    ->orWhere('role', 'like', $search)
                    ->orWhereHas('detail', function ($qDetail) use ($search) {
                        $qDetail->where('name', 'like', $search)
                            ->orWhere('no_hp', 'like', $search)
                            ->orWhere('nik', 'like', $search);
                    });
            });
        });
}

    protected function mapPreview($data)
    {
        return $data->map(fn($u) => [
            'email'         => $u->email,
            'name'          => $u->detail?->name,
            'role'          => $u->role,
            'credit_score'  => $u->credit_score,
            'restricted'    => (bool) $u->is_restricted,
            'activity_count' => $u->activity_logs_count,
            'recent_actions' => $u->activityLogs->pluck('action')->implode(', ') ?: 'none',
        ]);
    }
}
