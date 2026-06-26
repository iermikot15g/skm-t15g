<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_pertanyaan_survei_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pertanyaan_survei', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unsur_id')->constrained('unsur_survei')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->json('keterangan_skala')->nullable();
            $table->tinyInteger('urutan');
            $table->timestamps();
            $table->unique('urutan');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pertanyaan_survei');
    }
};