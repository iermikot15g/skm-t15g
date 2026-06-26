<?php
// app/Models/UnsurSurvei.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnsurSurvei extends Model
{
    protected $table = 'unsur_survei';
    
    protected $fillable = [
        'kode_unsur',
        'nama_unsur',
        'deskripsi',
        'skala_min',
        'skala_max'
    ];

    public function pertanyaans(): HasMany
    {
        return $this->hasMany(PertanyaanSurvei::class);
    }
}