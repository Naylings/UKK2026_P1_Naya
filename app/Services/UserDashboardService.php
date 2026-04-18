<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Appeal;
use App\Models\Loan;
use App\Models\Settlement;
use App\Models\Tool;
use App\Models\ToolReturn;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class UserDashboardService
{
    public function __construct(
        protected LoanService $loanService
    ) {}

    public function getDashboardData(array $filters = []): array
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        $userId = $user->id;

        return [
            'summary' => $this->getSummary($user),
            'active_loans' => $this->getActiveLoans($userId, $filters),
            'return_history' => $this->getReturnHistory($userId),
            'violations' => $this->getViolations($userId, $filters),
            'settlements' => $this->getSettlements($userId),
            'appeals' => $this->getAppeals($userId),
            'recommendations' => $this->getRecommendations($user),
            'alerts' => $this->getAlerts($userId),
            'recent_activities' => $this->getRecentActivities($userId),
        ];
    }

    private function getRecentActivities(int $userId): array
    {
        return ActivityLog::where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get([
                'id',
                'action',
                'module',
                'description',
                'created_at'
            ])
            ->toArray();
    }

    private function getAlerts(int $userId): array
    {
        $now = now()->toDateString();

        $activeLoan = Loan::where('user_id', $userId)
            ->where('status', 'approved')
            ->first();

        $isOverdue = false;
        $isDueSoon = false;

        if ($activeLoan) {
            $dueDate = $activeLoan->due_date->toDateString();

            $isOverdue = $dueDate < $now;
            $isDueSoon = $dueDate >= $now && $dueDate <= now()->addDays(2)->toDateString();
        }

        return [
            'has_overdue' => $isOverdue,
            'is_due_soon' => $isDueSoon,
            'has_active_violation' => Violation::where('user_id', $userId)
                ->where('status', 'active')
                ->exists(),
        ];
    }

    private function getSummary(User $user): array
    {
        return [
            'credit_score' => $user->credit_score,
            'is_restricted' => (bool) $user->is_restricted,
            'has_active_loan' => Loan::where('user_id', $user->id)
                ->where('status', 'approved')
                ->exists(),

            'total_violations_count' => Violation::where('user_id', $user->id)
                ->count(),
        ];
    }

    private function getActiveLoans(int $userId, array $filters): LengthAwarePaginator
    {
        return $this->loanService->getByUserId(
            $userId,
            array_merge($filters, ['status' => 'approved'])
        );
    }

    private function getReturnHistory(int $userId): array
    {
        return ToolReturn::with([
            'loan:id,tool_id,unit_code',
            'loan.tool:id,name',
            'loan.unit:code,tool_id'
        ])
            ->whereHas('loan', fn($q) => $q->where('user_id', $userId))
            ->latest('return_date')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getViolations(int $userId, array $filters): LengthAwarePaginator
    {
        return Violation::query()
            ->with(['settlement'])
            ->where('user_id', $userId)
            ->when(
                $filters['search'] ?? null,
                fn($q, $s) => $q->where('type', 'like', "%{$s}%")
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 5);
    }

    private function getSettlements(int $userId): array
    {
        return Settlement::with([
            'violation:id,loan_id,user_id',
            'violation.loan:id,tool_id',
            'violation.loan.tool:id,name'
        ])
            ->whereHas('violation', fn($q) => $q->where('user_id', $userId))
            ->latest('settled_at')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getAppeals(int $userId): array
    {
        return Appeal::where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getRecommendations(User $user): array
    {
        if ($user->is_restricted) {
            return [];
        }

        return Tool::where('min_credit_score', '<=', $user->credit_score)
            ->whereHas('units', fn($q) => $q->where('status', 'available'))
            ->with('category:id,name')
            ->limit(6)
            ->get()
            ->toArray();
    }
}
