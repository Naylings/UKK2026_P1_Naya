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

    
    
    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ToolUnit::class, 'unit_code', 'code');
    }

    public function toolReturn(): HasOne
    {
        return $this->hasOne(ToolReturn::class, 'loan_id');
    }

    public function violation(): HasOne
    {
        return $this->hasOne(Violation::class, 'loan_id');
    }

    
    
    

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

public function isApproved(): bool
    {
        return $this->status === 'approved';
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
        return $this->isApproved() && now()->toDateString() > $this->due_date->toDateString();
    }
}
