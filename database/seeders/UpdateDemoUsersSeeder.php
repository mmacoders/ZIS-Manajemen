<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UpdateDemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $bidang1Role = Role::where('name', 'bidang1')->first();
        $bidang2Role = Role::where('name', 'bidang2')->first();
        $bidang4Role = Role::where('name', 'bidang4')->first();

        // Update or create admin user
        User::updateOrCreate([
            'email' => 'admin@zis.com'
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role_id' => $adminRole ? $adminRole->id : 1,
            'phone' => '081234567890',
            'status' => 'active'
        ]);

        // Update or create bidang1 user
        User::updateOrCreate([
            'email' => 'bidang1@zis.com'
        ], [
            'name' => 'Bidang Pengumpulan',
            'password' => Hash::make('password'),
            'role_id' => $bidang1Role ? $bidang1Role->id : 2,
            'phone' => '081234567891',
            'status' => 'active'
        ]);

        // Update or create bidang2 user
        User::updateOrCreate([
            'email' => 'bidang2@zis.com'
        ], [
            'name' => 'Bidang Distribusi',
            'password' => Hash::make('password'),
            'role_id' => $bidang2Role ? $bidang2Role->id : 3,
            'phone' => '081234567892',
            'status' => 'active'
        ]);

        // Update or create bidang4 user
        User::updateOrCreate([
            'email' => 'bidang4@zis.com'
        ], [
            'name' => 'Bidang Arsip',
            'password' => Hash::make('password'),
            'role_id' => $bidang4Role ? $bidang4Role->id : 4,
            'phone' => '081234567894',
            'status' => 'active'
        ]);
    }
}