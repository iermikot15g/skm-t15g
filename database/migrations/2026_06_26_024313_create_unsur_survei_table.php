<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_unsur_survei_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unsur_survei', function (Blueprint $table) {
            $table->id();
            $table->char('kode_unsur', 1)->unique();
            $table->string('nama_unsur', 255);
            $table->text('deskripsi')->nullable();
            $table->tinyInteger('skala_min')->default(1);
            $table->tinyInteger('skala_max')->default(4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unsur_survei');
    }
};