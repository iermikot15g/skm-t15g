<?php
// app/Models/Layanan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Layanan extends Model
{
    protected $table = 'layanan';
    
    protected $fillable = [
        'opd_id',        // <- PASTIKAN ini ada
        'kode_layanan',
        'nama_layanan',
        'deskripsi',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi ke OPD - EXPLICIT foreign key
    public function opd(): BelongsTo
    {
        return $this->belongsTo(OPD::class, 'opd_id');
    }

    // Relasi ke SurveiResponse
    public function surveiResponses(): HasMany
    {
        return $this->hasMany(SurveiResponse::class, 'layanan_id');
    }
}