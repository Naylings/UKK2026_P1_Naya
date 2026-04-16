<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function log(
        string $action,
        string $module,
        string $description,
        array $meta = [],
        ?int $userId = null,
        ?string $ipAddress = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'meta' => empty($meta) ? null : $meta,
            'ip_address' => $ipAddress ?? request()?->ip(),
            'created_at' => now(),
        ]);
    }

    public function getAll(array $filters = [])
    {
        return ActivityLog::query()
            ->with('user.detail')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('action', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user.detail', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['module'] ?? null, function ($q, $module) {
                $q->where('module', $module);
            })
            ->when($filters['action'] ?? null, function ($q, $action) {
                $q->where('action', $action);
            })
            ->latest('created_at')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getById(int $id): ActivityLog
    {
        return ActivityLog::with('user.detail')->findOrFail($id);
    }
}
