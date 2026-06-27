<?php
// app/Models/SurveiResponse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveiResponse extends Model
{
    protected $table = 'survei_responses';
    
    protected $fillable = [
        'responden_id',
        'layanan_id',
        'periode_id',
        'kritik_saran',
        'ip_address',
        'user_agent',
        'reviewed_at',
        'submitted_at',
        'status'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function responden(): BelongsTo
    {
        return $this->belongsTo(Responden::class);
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeSurvei::class, 'periode_id');
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(JawabanSurvei::class, 'survei_response_id');
    }
}