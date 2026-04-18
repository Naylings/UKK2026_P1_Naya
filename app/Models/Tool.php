<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'name',
        'item_type',
        'price',
        'min_credit_score',
        'description',
        'code_slug',
        'photo_path',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    
    
    

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ToolUnit::class);
    }

    public function bundleComponents(): HasMany
    {
        return $this->hasMany(BundleTool::class, 'bundle_id');
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(BundleTool::class, 'tool_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    
    
    

    public function isSingle(): bool
    {
        return $this->item_type === 'single';
    }

    public function isBundle(): bool
    {
        return $this->item_type === 'bundle';
    }

    public function isBundleTool(): bool
    {
        return $this->item_type === 'bundle_tool';
    }
}
