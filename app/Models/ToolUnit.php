<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ToolUnit extends Model
{
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

    
    
    

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(UnitCondition::class, 'unit_code', 'code');
    }

    public function latestCondition(): HasOne
    {
        return $this->hasOne(UnitCondition::class, 'unit_code', 'code')
                    ->latestOfMany('recorded_at');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'unit_code', 'code');
    }

    
    
    

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
