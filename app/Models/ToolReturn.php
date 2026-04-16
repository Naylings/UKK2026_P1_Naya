<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ToolReturn extends Model
{
    protected $table = 'returns';

    public $timestamps = true;

    protected $fillable = [
        'loan_id',
        'employee_id',
        'return_date',
        'proof',
        'reviewed',
    ];

    protected $casts = [
    'return_date' => 'datetime',
    'reviewed' => 'boolean',
        'created_at'  => 'datetime',
    ];



    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function conditions()
    {
        return $this->hasMany(UnitCondition::class, 'return_id');
    }

    public function violation(): HasOne
    {
        return $this->hasOne(Violation::class, 'return_id');
    }
}
