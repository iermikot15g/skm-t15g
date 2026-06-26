<?php
// database/seeders/UnsurSurveiSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnsurSurvei;
use App\Models\PertanyaanSurvei;

class UnsurSurveiSeeder extends Seeder
{
    public function run()
    {
        $unsurs = [
            [
                'kode_unsur' => '1',
                'nama_unsur' => 'Persyaratan',
                'deskripsi' => 'Persyaratan adalah syarat yang harus dipenuhi dalam pengurusan suatu jenis pelayanan',
            ],
            [
                'kode_unsur' => '2',
                'nama_unsur' => 'Prosedur',
                'deskripsi' => 'Prosedur adalah tata cara pelayanan yang dibakukan bagi pemberi dan penerima pelayanan',
            ],
            [
                'kode_unsur' => '3',
                'nama_unsur' => 'Waktu Pelayanan',
                'deskripsi' => 'Waktu pelayanan adalah jangka waktu yang diperlukan untuk menyelesaikan seluruh proses pelayanan',
            ],
            [
                'kode_unsur' => '4',
                'nama_unsur' => 'Biaya/Tarif',
                'deskripsi' => 'Biaya/tarif adalah ongkos yang dikenakan kepada penerima layanan',
            ],
            [
                'kode_unsur' => '5',
                'nama_unsur' => 'Produk Spesifikasi',
                'deskripsi' => 'Produk spesifikasi jenis pelayanan adalah hasil pelayanan yang diberikan sesuai dengan ketentuan',
            ],
            [
                'kode_unsur' => '6',
                'nama_unsur' => 'Kompetensi Pelaksana',
                'deskripsi' => 'Kompetensi pelaksana adalah kemampuan yang harus dimiliki oleh pelaksana',
            ],
            [
                'kode_unsur' => '7',
                'nama_unsur' => 'Perilaku Pelaksana',
                'deskripsi' => 'Perilaku pelaksana adalah sikap petugas dalam memberikan pelayanan',
            ],
            [
                'kode_unsur' => '8',
                'nama_unsur' => 'Sarana dan Prasarana',
                'deskripsi' => 'Sarana dan prasarana adalah fasilitas pendukung dalam pelayanan',
            ],
            [
                'kode_unsur' => '9',
                'nama_unsur' => 'Penanganan Pengaduan',
                'deskripsi' => 'Penanganan pengaduan adalah tata cara penanganan pengaduan dan saran',
            ],
        ];

        $pertanyaans = [
            [
                'unsur_kode' => '1',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap kesesuaian persyaratan pelayanan dengan jenis pelayanannya?',
                'keterangan_skala' => json_encode(['1' => 'Tidak sesuai', '2' => 'Kurang sesuai', '3' => 'Sesuai', '4' => 'Sangat sesuai']),
                'urutan' => 1,
            ],
            [
                'unsur_kode' => '2',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap kemudahan prosedur pelayanan di unit ini?',
                'keterangan_skala' => json_encode(['1' => 'Tidak mudah', '2' => 'Kurang mudah', '3' => 'Mudah', '4' => 'Sangat mudah']),
                'urutan' => 2,
            ],
            [
                'unsur_kode' => '3',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap kecepatan waktu pelayanan di unit ini?',
                'keterangan_skala' => json_encode(['1' => 'Tidak cepat', '2' => 'Kurang cepat', '3' => 'Cepat', '4' => 'Sangat cepat']),
                'urutan' => 3,
            ],
            [
                'unsur_kode' => '4',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap kewajaran biaya/tarif pelayanan di unit ini?',
                'keterangan_skala' => json_encode(['1' => 'Sangat mahal', '2' => 'Cukup mahal', '3' => 'Murah', '4' => 'Gratis']),
                'urutan' => 4,
            ],
            [
                'unsur_kode' => '5',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap kesesuaian hasil pelayanan dengan yang dijanjikan?',
                'keterangan_skala' => json_encode(['1' => 'Tidak sesuai', '2' => 'Kurang sesuai', '3' => 'Sesuai', '4' => 'Sangat sesuai']),
                'urutan' => 5,
            ],
            [
                'unsur_kode' => '6',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap kompetensi/kemampuan petugas dalam memberikan pelayanan?',
                'keterangan_skala' => json_encode(['1' => 'Tidak kompeten', '2' => 'Kurang kompeten', '3' => 'Kompeten', '4' => 'Sangat kompeten']),
                'urutan' => 6,
            ],
            [
                'unsur_kode' => '7',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap perilaku petugas dalam memberikan pelayanan (kesopanan & keramahan)?',
                'keterangan_skala' => json_encode(['1' => 'Tidak sopan', '2' => 'Kurang sopan', '3' => 'Sopan', '4' => 'Sangat sopan']),
                'urutan' => 7,
            ],
            [
                'unsur_kode' => '8',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap kualitas sarana dan prasarana pendukung pelayanan?',
                'keterangan_skala' => json_encode(['1' => 'Buruk', '2' => 'Cukup', '3' => 'Baik', '4' => 'Sangat baik']),
                'urutan' => 8,
            ],
            [
                'unsur_kode' => '9',
                'pertanyaan' => 'Bagaimana penilaian Anda terhadap penanganan pengaduan, saran, dan masukan?',
                'keterangan_skala' => json_encode(['1' => 'Tidak tersedia', '2' => 'Tersedia tidak berfungsi', '3' => 'Kurang maksimal', '4' => 'Dikelola baik']),
                'urutan' => 9,
            ],
        ];

        foreach ($unsurs as $unsurData) {
            $unsur = UnsurSurvei::create($unsurData);
            
            // Cari pertanyaan yang sesuai
            $pertanyaanData = collect($pertanyaans)->firstWhere('unsur_kode', $unsurData['kode_unsur']);
            if ($pertanyaanData) {
                PertanyaanSurvei::create([
                    'unsur_id' => $unsur->id,
                    'pertanyaan' => $pertanyaanData['pertanyaan'],
                    'keterangan_skala' => $pertanyaanData['keterangan_skala'],
                    'urutan' => $pertanyaanData['urutan'],
                ]);
            }
        }
    }
}