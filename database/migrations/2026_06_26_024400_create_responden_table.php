<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_responden_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responden', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 255)->unique(); // Encrypted
            $table->string('nama', 100);
            $table->string('hp', 255); // Encrypted
            $table->integer('usia');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('pendidikan', 50);
            $table->string('pekerjaan', 50);
            $table->string('pekerjaan_lainnya', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responden');
    }
};