<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table = 'app_configs';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'late_point',
        'broken_point',
        'lost_point',
        'late_fine',
        'broken_fine',
        'lost_fine',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    // AppConfig tidak memiliki relasi ke tabel lain.
    // Diakses oleh Service layer sebagai konfigurasi global aplikasi.
}
