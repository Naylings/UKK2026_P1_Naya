<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UnitCondition extends Model
{
    /**
     * Primary key adalah kode string yang di-generate oleh BE.
     */
    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'unit_code',
        'return_id',
        'conditions',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ToolUnit::class, 'unit_code', 'code');
    }

    /**
     * Return yang menghasilkan catatan kondisi ini.
     * NULL jika dicatat di luar konteks pengembalian (entry awal, maintenance, inspeksi).
     */
    public function toolReturn(): BelongsTo
    {
        return $this->belongsTo(ToolReturn::class, 'return_id');
    }

    /** Return yang secara eksplisit FK-nya mengarah ke kondisi ini */
    public function returnRecord(): HasOne
    {
        return $this->hasOne(ToolReturn::class, 'condition_id', 'id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isGood(): bool
    {
        return $this->conditions === 'good';
    }

    public function isBroken(): bool
    {
        return $this->conditions === 'broken';
    }

    public function isMaintenance(): bool
    {
        return $this->conditions === 'maintenance';
    }
}
