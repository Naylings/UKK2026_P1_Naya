<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Loan;
use App\Models\Settlement;
use App\Models\Tool;
use App\Models\ToolReturn;
use App\Models\User;
use App\Models\Violation;

class AdminDashboardService
{
    public function getDashboardData(): array
    {
        return [
            'summary' => $this->getSummary(),
            'alerts' => $this->getAlerts(),
            'pending' => $this->getPending(),
            'recent_activities' => $this->getRecentActivities(),
            'stats' => $this->getTodayStats(),
        ];
    }

    private function getSummary(): array
    {
        return [
            'total_users' => User::count(),
            'total_tools' => Tool::count(),

            'active_loans' => Loan::where('status', 'approved')->count(),
            'pending_loans' => Loan::where('status', 'pending')->count(),

            'pending_returns' => ToolReturn::where('reviewed', false)->count(),

            'active_violations' => Violation::where('status', 'active')->count(),
        ];
    }

    private function getAlerts(): array
    {
        $today = now()->toDateString();

        return [
            'overdue_loans' => Loan::where('status', 'approved')
                ->where('due_date', '<', $today)
                ->count(),

            'due_today' => Loan::where('status', 'approved')
                ->where('due_date', $today)
                ->count(),

            'unreviewed_returns' => ToolReturn::where('reviewed', false)->count(),

            'unsettled_violations' => Violation::where('status', 'active')->count(),
        ];
    }

    private function getPending(): array
    {
        return [
            'loans' => $this->getPendingLoans(),
            'returns' => $this->getPendingReturns(),
            'violations' => $this->getPendingViolations(),
        ];
    }

    private function getPendingLoans(): array
    {
        return Loan::with([
                'user:id,email',
                'tool:id,name',
            ])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'user_id',
                'tool_id',
                'loan_date',
                'due_date',
                'status'
            ])
            ->toArray();
    }

    private function getPendingReturns(): array
    {
        return ToolReturn::with([
                'loan:id,user_id,tool_id',
                'loan.user:id,email',
                'loan.tool:id,name',
            ])
            ->where('reviewed', false)
            ->latest('return_date')
            ->limit(5)
            ->get([
                'id',
                'loan_id',
                'return_date',
                'reviewed'
            ])
            ->toArray();
    }

    private function getPendingViolations(): array
    {
        return Violation::with([
                'loan:id,user_id',
                'loan.user:id,email',
            ])
            ->where('status', 'active')
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'loan_id',
                'type',
                'fine',
                'status',
                'created_at'
            ])
            ->toArray();
    }

    private function getRecentActivities(): array
    {
        return ActivityLog::with('user:id,email')
            ->latest()
            ->limit(10)
            ->get([
                'id',
                'user_id',
                'action',
                'module',
                'description',
                'created_at'
            ])
            ->toArray();
    }

    private function getTodayStats(): array
    {
        $today = now()->toDateString();

        return [
            'returns_today' => ToolReturn::whereDate('return_date', $today)->count(),

            'settlements_today' => Settlement::whereDate('settled_at', $today)->count(),
        ];
    }
}