<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya jalankan production seeder
        $this->call([
            ProductionSeeder::class,
        ]);

        // ⚠️ KOMENTAR atau HAPUS seeder berikut untuk production:
        // $this->call([OPDSeeder::class]);
        // $this->call([PeriodeSurveiSeeder::class]);
        // $this->call([UnsurSurveiSeeder::class]);
        // $this->call([SurveiTestSeeder::class]);
    }
}