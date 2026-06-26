<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_survei_responses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survei_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responden_id')->constrained('responden')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_survei')->cascadeOnDelete();
            $table->text('kritik_saran')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->enum('status', ['draft', 'completed'])->default('completed');
            $table->timestamps();
            
            $table->unique(['responden_id', 'layanan_id', 'periode_id'], 'unique_survei');
            $table->index('submitted_at');
            $table->index('ip_address');
        });
    }

    public function down()
    {
        Schema::dropIfExists('survei_responses');
    }
};