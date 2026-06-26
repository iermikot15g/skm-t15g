<?php
// database/migrations/2026_06_25_122856_create_layanan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;  // <- PASTIKAN ini ada
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')
                  ->constrained('opd')
                  ->cascadeOnDelete();
            $table->string('kode_layanan', 50);
            $table->string('nama_layanan', 255);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['opd_id', 'kode_layanan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};