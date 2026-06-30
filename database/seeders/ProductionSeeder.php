<?php
// database/seeders/ProductionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat role (jika belum ada)
        $this->createRoles();

        // 2. Buat Super Admin
        $this->createSuperAdmin();

        // 3. OPD, Layanan, Periode, Data Survei TIDAK dibuat
        // (biarkan kosong untuk diisi oleh Super Admin nanti)
    }

    private function createRoles()
    {
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrator'],
            ['name' => 'admin_opd', 'display_name' => 'Admin Unit/OPD'],
            ['name' => 'pimpinan_opd', 'display_name' => 'Pimpinan Unit/OPD'],
            ['name' => 'pimpinan_utama', 'display_name' => 'Pimpinan Utama'],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(
                ['name' => $role['name']],
                [
                    'display_name' => $role['display_name'],
                    'description' => 'Role ' . $role['display_name'],
                    'permissions' => json_encode([]),
                ]
            );
        }

        $this->command->info('✅ Roles created successfully!');
    }

    private function createSuperAdmin()
    {
        $role = Role::where('name', 'super_admin')->first();

        if (!$role) {
            $this->command->error('❌ Role super_admin not found!');
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@sumenep.go.id'],
            [
                'name' => 'Super Admin SKM',
                'password' => Hash::make('P@ssw0rd2024!'),
                'role_id' => $role->id,
                'opd_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Super Admin created!');
        $this->command->info('   Email: admin@sumenep.go.id');
        $this->command->info('   Password: P@ssw0rd2024!');
        $this->command->warn('   ⚠️  Change this password immediately after first login!');
    }
}