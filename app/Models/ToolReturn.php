<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ToolReturn extends Model
{
    /**
     * Nama tabel eksplisit karena nama model bukan "Return"
     * (return adalah reserved word PHP).
     */
    protected $table = 'returns';

    public $timestamps = false;

    protected $fillable = [
        'loan_id',
        'employee_id',
        'condition_id',
        'return_date',
        'proof',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'return_date' => 'date',
        'created_at'  => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /** Employee yang mencatat pengembalian */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /** Kondisi alat saat dikembalikan */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(UnitCondition::class, 'condition_id', 'id');
    }

    /** Pelanggaran yang terkait dengan pengembalian ini (jika ada) */
    public function violation(): HasOne
    {
        return $this->hasOne(Violation::class, 'return_id');
    }
}
