<?php

namespace App\Models;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'email',
        'password',
        'role',
        'credit_score',
        'is_restricted',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_restricted' => 'boolean',
    ];

    
    
    

    public function detail(): HasOne
    {
        return $this->hasOne(UserDetail::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'user_id');
    }

    public function handledLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'employee_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ToolReturn::class, 'employee_id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'user_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class, 'employee_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class, 'user_id');
    }

    public function reviewedAppeals(): HasMany
    {
        return $this->hasMany(Appeal::class, 'reviewed_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    
    
    

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'Employee';
    }

    public function isUser(): bool
    {
        return $this->role === 'User';
    }
    
    
    
    
    
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role'  => $this->role,
            'email' => $this->email,
        ];
    }
}
