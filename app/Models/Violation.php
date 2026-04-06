<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Violation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'loan_id',
        'user_id',
        'return_id',
        'type',
        'total_score',
        'fine',
        'description',
        'status',
        'created_at',
    ];

    protected $casts = [
        'fine'       => 'float',
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /** User yang dikenakan pelanggaran */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return terkait pelanggaran ini.
     * NULL jika type = lost (tidak ada proses pengembalian).
     */
    public function toolReturn(): BelongsTo
    {
        return $this->belongsTo(ToolReturn::class, 'return_id');
    }

    /** Settlement (pelunasan) dari pelanggaran ini (1-to-1) */
    public function settlement(): HasOne
    {
        return $this->hasOne(Settlement::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSettled(): bool
    {
        return $this->status === 'settled';
    }

    public function isLate(): bool
    {
        return $this->type === 'late';
    }

    public function isDamaged(): bool
    {
        return $this->type === 'damaged';
    }

    public function isLost(): bool
    {
        return $this->type === 'lost';
    }
}
