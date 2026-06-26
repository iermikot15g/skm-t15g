<?php
// database/seeders/PeriodeSurveiSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodeSurvei;
use App\Models\User;

class PeriodeSurveiSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('email', 'admin@sumenep.go.id')->first();
        
        if (!$superAdmin) {
            $this->command->error('Super Admin tidak ditemukan!');
            return;
        }

        PeriodeSurvei::create([
            'nama_periode' => 'Periode Survei 2026',
            'deskripsi' => 'Periode survei kepuasan masyarakat tahun 2026',
            'tanggal_mulai' => now()->startOfMonth(),
            'tanggal_selesai' => now()->endOfMonth(),
            'is_active' => true,
            'created_by' => $superAdmin->id,
        ]);

        $this->command->info('Periode survei aktif berhasil dibuat!');
    }
}