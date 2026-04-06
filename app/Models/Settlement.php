<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'violation_id',
        'employee_id',
        'description',
        'settled_at',
    ];

    protected $casts = [
        'settled_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    /** Employee yang mencatat pelunasan */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
