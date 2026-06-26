<?php
// app/Models/PeriodeSurvei.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodeSurvei extends Model
{
    protected $table = 'periode_survei';
    
    protected $fillable = [
        'nama_periode',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active && 
               now()->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    // Scope aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('tanggal_mulai', '<=', now())
                     ->where('tanggal_selesai', '>=', now());
    }
}