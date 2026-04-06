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

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    /** Bundle (tools dengan item_type = bundle) yang memuat sub-tool ini */
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'bundle_id');
    }

    /** Sub-tool (tools dengan item_type = bundle_tool) yang ada di bundle */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'tool_id');
    }
}
