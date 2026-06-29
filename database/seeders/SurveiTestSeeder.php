<?php
// database/seeders/SurveiTestSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{
    Responden,
    SurveiResponse,
    JawabanSurvei,
    Layanan,
    PeriodeSurvei
};
use Illuminate\Support\Facades\Crypt;

class SurveiTestSeeder extends Seeder
{
    public function run()
    {
        $periode = PeriodeSurvei::where('is_active', true)->first();
        if (!$periode) {
            $this->command->error('Tidak ada periode aktif!');
            return;
        }

        $layanans = Layanan::where('is_active', true)->get();
        if ($layanans->isEmpty()) {
            $this->command->error('Tidak ada layanan aktif!');
            return;
        }

        // Data responden contoh
        $respondenData = [
            [
                'nik' => '3529123412341234',
                'nama' => 'Budi Santoso',
                'hp' => '081234567890',
                'usia' => 30,
                'jenis_kelamin' => 'L',
                'pendidikan' => 'S1',
                'pekerjaan' => 'PNS',
            ],
            [
                'nik' => '3529567890123456',
                'nama' => 'Siti Rahayu',
                'hp' => '081298765432',
                'usia' => 25,
                'jenis_kelamin' => 'P',
                'pendidikan' => 'D4/S1',
                'pekerjaan' => 'Karyawan Swasta',
            ],
            [
                'nik' => '3529789012345678',
                'nama' => 'Agus Wijaya',
                'hp' => '081256789012',
                'usia' => 45,
                'jenis_kelamin' => 'L',
                'pendidikan' => 'S2',
                'pekerjaan' => 'Wiraswasta',
            ],
        ];

        // Data jawaban contoh (9 unsur)
        $jawabanExamples = [
            [4, 3, 4, 3, 4, 3, 4, 3, 4], // Sangat Baik
            [3, 3, 3, 3, 3, 3, 3, 3, 3], // Baik
            [2, 3, 2, 3, 2, 3, 2, 3, 2], // Cukup
            [4, 4, 4, 3, 3, 4, 4, 3, 3], // Variatif
            [3, 4, 3, 4, 3, 4, 3, 4, 3], // Variatif
        ];

        foreach ($respondenData as $index => $data) {
            // Buat responden
            $responden = Responden::create([
                'nik' => Crypt::encryptString($data['nik']),
                'nama' => $data['nama'],
                'hp' => Crypt::encryptString($data['hp']),
                'usia' => $data['usia'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'pendidikan' => $data['pendidikan'],
                'pekerjaan' => $data['pekerjaan'],
                'pekerjaan_lainnya' => null,
            ]);

            // Pilih layanan (bergantian)
            $layanan = $layanans[$index % $layanans->count()];

            // Buat survei response
            $survei = SurveiResponse::create([
                'responden_id' => $responden->id,
                'layanan_id' => $layanan->id,
                'periode_id' => $periode->id,
                'kritik_saran' => 'Pelayanan sudah baik, tapi perlu peningkatan kecepatan.',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'reviewed_at' => now(),
                'submitted_at' => now(),
                'status' => 'completed',
            ]);

            // Pilih jawaban contoh
            $jawaban = $jawabanExamples[$index % count($jawabanExamples)];

            // Buat jawaban untuk 9 unsur
            for ($i = 1; $i <= 9; $i++) {
                JawabanSurvei::create([
                    'survei_response_id' => $survei->id,
                    'pertanyaan_id' => $i,
                    'nilai' => $jawaban[$i - 1],
                ]);
            }

            $this->command->info("Responden {$data['nama']} berhasil ditambahkan!");
        }

        $this->command->info('Data survei test berhasil dibuat!');
    }
}