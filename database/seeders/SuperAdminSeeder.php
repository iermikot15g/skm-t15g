<?php
// database/seeders/SuperAdminSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Cari role super_admin
        $role = Role::where('name', 'super_admin')->first();

        if (!$role) {
            $this->command->error('Role super_admin tidak ditemukan! Jalankan RoleSeeder dulu.');
            return;
        }

        // Buat Super Admin
        User::updateOrCreate(
            ['email' => 'admin@sumenep.go.id'],
            [
                'name' => 'Super Admin SKM',
                'email' => 'admin@sumenep.go.id',
                'password' => Hash::make('P@ssw0rd2024!'),
                'role_id' => $role->id,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super Admin created:');
        $this->command->info('Email: admin@sumenep.go.id');
        $this->command->info('Password: P@ssw0rd2024!');
    }
}