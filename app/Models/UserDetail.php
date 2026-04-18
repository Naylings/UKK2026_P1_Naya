<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    protected $primaryKey = 'nik';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'nik',
        'user_id',
        'name',
        'no_hp',
        'address',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    
    
    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
