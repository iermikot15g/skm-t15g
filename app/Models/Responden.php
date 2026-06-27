<?php
// app/Models/Responden.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Responden extends Model
{
    protected $table = 'responden';
    
    protected $fillable = [
        'nik',
        'nama',
        'hp',
        'usia',
        'jenis_kelamin',
        'pendidikan',
        'pekerjaan',
        'pekerjaan_lainnya'
    ];

    public function surveiResponses(): HasMany
    {
        return $this->hasMany(SurveiResponse::class, 'responden_id');
    }
}