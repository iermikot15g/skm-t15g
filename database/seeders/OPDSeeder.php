<?php
// database/seeders/OPDSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OPD;
use App\Models\Layanan;
use Illuminate\Support\Facades\DB;

class OPDSeeder extends Seeder
{
    public function run(): void
    {
        // Buat beberapa OPD
        $opds = [
            [
                'kode_opd' => 'DINDIK',
                'nama_opd' => 'Dinas Pendidikan',
                'alamat' => 'Jl. Pendidikan No. 1, Sumenep',
                'kontak' => '0328-123456',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_opd' => 'DINKES',
                'nama_opd' => 'Dinas Kesehatan',
                'alamat' => 'Jl. Kesehatan No. 2, Sumenep',
                'kontak' => '0328-234567',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_opd' => 'DUKCAPIL',
                'nama_opd' => 'Dinas Kependudukan dan Pencatatan Sipil',
                'alamat' => 'Jl. Kartini No. 3, Sumenep',
                'kontak' => '0328-345678',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($opds as $opdData) {
            // Insert OPD dan dapatkan ID
            $opdId = DB::table('opd')->insertGetId($opdData);
            
            // Buat layanan untuk setiap OPD menggunakan DB facade
            $layanans = match($opdData['kode_opd']) {
                'DINDIK' => [
                    [
                        'opd_id' => $opdId,
                        'kode_layanan' => 'IJAZAH',
                        'nama_layanan' => 'Penerbitan Ijazah',
                        'deskripsi' => 'Penerbitan ijazah untuk lulusan sekolah',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'opd_id' => $opdId,
                        'kode_layanan' => 'KIP',
                        'nama_layanan' => 'Kartu Indonesia Pintar',
                        'deskripsi' => 'Pendaftaran dan pencairan KIP',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
                'DINKES' => [
                    [
                        'opd_id' => $opdId,
                        'kode_layanan' => 'BPJS',
                        'nama_layanan' => 'Pendaftaran BPJS Kesehatan',
                        'deskripsi' => 'Pendaftaran peserta BPJS Kesehatan',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'opd_id' => $opdId,
                        'kode_layanan' => 'IMUNISASI',
                        'nama_layanan' => 'Pelayanan Imunisasi',
                        'deskripsi' => 'Program imunisasi untuk balita',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
                'DUKCAPIL' => [
                    [
                        'opd_id' => $opdId,
                        'kode_layanan' => 'KTP',
                        'nama_layanan' => 'Pembuatan KTP',
                        'deskripsi' => 'Pembuatan KTP elektronik',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'opd_id' => $opdId,
                        'kode_layanan' => 'AKTA',
                        'nama_layanan' => 'Pembuatan Akta Kelahiran',
                        'deskripsi' => 'Pembuatan akta kelahiran untuk bayi',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
                default => [],
            };

            foreach ($layanans as $layanan) {
                DB::table('layanan')->insert($layanan);
            }
        }
    }
}