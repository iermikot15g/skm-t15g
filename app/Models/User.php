<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'opd_id',  // <-- PASTIKAN INI ADA
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(OPD::class, 'opd_id');
    }

    // Helper methods
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdminOPD(): bool
    {
        return $this->hasRole('admin_opd');
    }

    public function isPimpinanOPD(): bool
    {
        return $this->hasRole('pimpinan_opd');
    }

    public function isPimpinanUtama(): bool
    {
        return $this->hasRole('pimpinan_utama');
    }
}