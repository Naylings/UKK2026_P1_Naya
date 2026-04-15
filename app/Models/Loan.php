<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'tool_id',
        'unit_code',
        'employee_id',
        'status',
        'loan_date',
        'due_date',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date'  => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    /** User yang mengajukan peminjaman */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Employee yang memproses (approve/reject) */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /** Template alat yang dipinjam */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /** Unit fisik spesifik yang dipinjam */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(ToolUnit::class, 'unit_code', 'code');
    }

    /** Pengembalian terkait loan ini (1-to-1) */
    public function toolReturn(): HasOne
    {
        return $this->hasOne(ToolReturn::class, 'loan_id');
    }

    /** Pelanggaran yang lahir dari loan ini */
    public function violation(): HasOne
    {
        return $this->hasOne(Violation::class, 'loan_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

public function isApprove(): bool
    {
        return $this->status === 'approve';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isOverdue(): bool
    {
return $this->isApprove() && now()->toDateString() > $this->due_date->toDateString();
    }
}
