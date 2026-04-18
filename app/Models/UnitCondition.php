<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UnitCondition extends Model
{
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

    
    
    

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ToolUnit::class, 'unit_code', 'code');
    }

    public function toolReturn(): BelongsTo
    {
        return $this->belongsTo(ToolReturn::class, 'return_id');
    }

    public function returnRecord(): HasOne
    {
        return $this->hasOne(ToolReturn::class, 'condition_id', 'id');
    }

    
    
    

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
