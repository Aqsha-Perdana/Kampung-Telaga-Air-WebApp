<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Check if user is admin (can access Master Data & Transaction)
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is pengelola (can only access Financial)
    public function isPengelola(): bool
    {
        return $this->role === 'pengelola';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    // Check if user can access Master Data menu
    public function canAccessMasterData(): bool
    {
        return $this->isAdmin();
    }

    // Check if user can access Transaction menu
    public function canAccessTransaction(): bool
    {
        return $this->isAdmin();
    }

    // Check if user can access Financial menu
    public function canAccessFinancial(): bool
    {
        return $this->isAdmin() || $this->isPengelola();
    }
}