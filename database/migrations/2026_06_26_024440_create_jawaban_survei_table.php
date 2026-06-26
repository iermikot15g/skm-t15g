<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_jawaban_survei_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jawaban_survei', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survei_response_id')->constrained('survei_responses')->cascadeOnDelete();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan_survei')->cascadeOnDelete();
            $table->tinyInteger('nilai')->check('nilai BETWEEN 1 AND 4');
            $table->timestamps();
            
            $table->unique(['survei_response_id', 'pertanyaan_id'], 'unique_jawaban');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jawaban_survei');
    }
};