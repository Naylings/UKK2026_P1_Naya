<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ToolUnit extends Model
{
    /**
     * Primary key adalah kode string (contoh: LPT-001, SET-PK-001).
     */
    protected $primaryKey = 'code';
    public $incrementing  = false;
    protected $keyType    = 'string';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'tool_id',
        'status',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(UnitCondition::class, 'unit_code', 'code');
    }

    /** Kondisi terkini berdasarkan recorded_at terbaru */
    public function latestCondition(): HasOne
    {
        return $this->hasOne(UnitCondition::class, 'unit_code', 'code')
                    ->latestOfMany('recorded_at');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'unit_code', 'code');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isLent(): bool
    {
        return $this->status === 'lent';
    }

    public function isNonactive(): bool
    {
        return $this->status === 'nonactive';
    }
}
