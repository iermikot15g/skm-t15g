<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Pengelola aplikasi (Diskominfo) - akses penuh',
                'permissions' => ['*'], // All permissions
            ],
            [
                'name' => 'admin_opd',
                'display_name' => 'Admin Unit/OPD',
                'description' => 'Petugas OPD yang mengelola layanan dan melihat hasil survei',
                'permissions' => ['manage_layanan', 'view_survei', 'view_reports'],
            ],
            [
                'name' => 'pimpinan_opd',
                'display_name' => 'Pimpinan Unit/OPD',
                'description' => 'Kepala OPD yang melihat hasil survei unitnya',
                'permissions' => ['view_survei', 'view_reports'],
            ],
            [
                'name' => 'pimpinan_utama',
                'display_name' => 'Pimpinan Utama',
                'description' => 'Bupati, Wakil Bupati, Sekda, Asisten - melihat semua OPD',
                'permissions' => ['view_all_survei', 'view_all_reports'],
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}