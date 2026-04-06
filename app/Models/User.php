<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'email',
        'password',
        'role',
        'credit_score',
        'is_restricted',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_restricted' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function detail(): HasOne
    {
        return $this->hasOne(UserDetail::class);
    }

    /** Peminjaman yang diajukan oleh user ini */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'user_id');
    }

    /** Peminjaman yang di-approve / reject oleh employee ini */
    public function handledLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'employee_id');
    }

    /** Pengembalian yang dicatat oleh employee ini */
    public function returns(): HasMany
    {
        return $this->hasMany(ToolReturn::class, 'employee_id');
    }

    /** Pelanggaran yang diterima user ini */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'user_id');
    }

    /** Settlement yang dicatat oleh employee ini */
    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class, 'employee_id');
    }

    /** Banding yang diajukan user ini */
    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class, 'user_id');
    }

    /** Banding yang di-review oleh admin ini */
    public function reviewedAppeals(): HasMany
    {
        return $this->hasMany(Appeal::class, 'reviewed_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'Employee';
    }

    public function isUser(): bool
    {
        return $this->role === 'User';
    }
}
