<?php
// app/Models/OPD.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OPD extends Model
{
    protected $table = 'opd';
    
    protected $fillable = [
        'kode_opd',
        'nama_opd',
        'alamat',
        'kontak',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi ke Layanan - EXPLICIT foreign key
    public function layanans(): HasMany
    {
        return $this->hasMany(Layanan::class, 'opd_id');
    }

    // Relasi ke Users
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'opd_id');
    }

    // Scope aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}