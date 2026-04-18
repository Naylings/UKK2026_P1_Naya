<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleTool extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'bundle_id',
        'tool_id',
        'qty',
    ];

    
    
    

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'bundle_id');
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'tool_id');
    }
}
