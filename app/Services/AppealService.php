<?php

namespace App\Services;

use App\Models\Appeal;
use App\Models\User;
use App\Exceptions\AppealException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppealService
{
    public function create(array $data): Appeal
    {
        $user = Auth::user();

        if (Appeal::where('user_id', $user->id)->where('status', 'pending')->exists()) {
            throw AppealException::recentAppealExists();
        }

        $appeal = DB::transaction(function () use ($data, $user) {
            return Appeal::create([
                'user_id' => $user->id,
                'reason' => $data['reason'],
                'status' => 'pending',
                'created_at' => now(),
            ]);
        });

        app(ActivityLogService::class)->log(
            'appeal.created',
            'appeals',
            "User mengajukan appeal
            ['appeal_id' => $appeal->id]
        );

        return $appeal->fresh(['user']);
    }

    public function getAll(array $filters = [])
    {
        return Appeal::query()
            ->with(['user.detail', 'reviewer.detail'])
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('reason', 'like', "%{$search}%")
                        ->orWhereHas('user.detail', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getByUserId(int $userId, array $filters = [])
    {
        return Appeal::query()
            ->with(['user.detail', 'reviewer.detail'])
            ->where('user_id', $userId)
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('reason', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function review(int $appealId, array $data): Appeal
    {
        $reviewer = Auth::user();
        if ($reviewer->role !== 'Admin') {
            throw AppealException::userNotAllowed();
        }

        return DB::transaction(function () use ($appealId, $data, $reviewer) {
            $appeal = Appeal::lockForUpdate()->find($appealId);

            if (!$appeal) {
                throw AppealException::notFound();
            }

            if (!$appeal->isPending()) {
                throw AppealException::notPending();
            }

            $status = $data['status'];
            $updateData = [
                'status' => $status,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ];

            if ($status === 'approved') {
                if (!isset($data['credit_changed']) || !is_numeric($data['credit_changed'])) {
                    throw AppealException::invalidStatusTransition();
                }
                $updateData['credit_changed'] = (int) $data['credit_changed'];
                $updateData['admin_notes'] = $data['admin_notes'] ?? null;

                $appeal->user->increment('credit_score', $updateData['credit_changed']);
            } elseif ($status === 'rejected') {
                $updateData['admin_notes'] = $data['admin_notes'] ?? 'Ditolak tanpa alasan khusus.';
            }

            $appeal->update($updateData);

            $action = $status === 'approved' ? 'appeal.approved' : 'appeal.rejected';
            app(ActivityLogService::class)->log(
                $action,
                'appeals',
                "Admin {$reviewer->email} " . ($status === 'approved' ? 'menyetujui' : 'menolak') . " appeal
                [
                    'appeal_id' => $appeal->id,
                    'status' => $status,
                    'credit_changed' => $updateData['credit_changed'] ?? null,
                ]
            );

            return $appeal->fresh(['user', 'reviewer']);
        });
    }
}
