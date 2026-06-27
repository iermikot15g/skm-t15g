<?php
// app/Models/JawabanSurvei.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanSurvei extends Model
{
    protected $table = 'jawaban_survei';
    
    protected $fillable = [
        'survei_response_id',
        'pertanyaan_id',
        'nilai'
    ];

    public function surveiResponse(): BelongsTo
    {
        return $this->belongsTo(SurveiResponse::class, 'survei_response_id');
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanSurvei::class, 'pertanyaan_id');
    }
}