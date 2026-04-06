<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'meta',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'meta'       => 'array',   // JSON string → array otomatis
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    /**
     * User yang melakukan aksi.
     * NULL jika aksi otomatis oleh sistem.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
