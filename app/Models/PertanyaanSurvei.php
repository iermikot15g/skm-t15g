<?php
// app/Models/PertanyaanSurvei.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PertanyaanSurvei extends Model
{
    protected $table = 'pertanyaan_survei';
    
    protected $fillable = [
        'unsur_id',
        'pertanyaan',
        'keterangan_skala',
        'urutan'
    ];

    protected $casts = [
        'keterangan_skala' => 'array',
    ];

    public function unsur(): BelongsTo
    {
        return $this->belongsTo(UnsurSurvei::class);
    }
}