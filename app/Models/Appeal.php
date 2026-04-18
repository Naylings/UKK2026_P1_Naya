<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appeal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'reviewed_by',
        'reason',
        'status',
        'credit_changed',
        'admin_notes',
        'created_at',
        'reviewed_at',
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    
    
    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
}
