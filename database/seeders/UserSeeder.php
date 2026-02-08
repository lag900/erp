<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@batu.edu.eg'],
            ['name' => 'Department Admin', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['Admin']);

        $manager = User::updateOrCreate(
            ['email' => 'manager@batu.edu.eg'],
            ['name' => 'Department Manager', 'password' => Hash::make('password')]
        );
        $manager->syncRoles(['Manager']);

        $dataEntry = User::updateOrCreate(
            ['email' => 'staff@batu.edu.eg'],
            ['name' => 'Staff Entry', 'password' => Hash::make('password')]
        );
        $dataEntry->syncRoles(['Data Entry']);

        // Keep SuperAdmin
        $user = User::updateOrCreate(
            ['email' => 'abdozero2030@gmail.com'],
            ['name' => 'Abdelrhman Ibrahim Sayed', 'password' => Hash::make('1234')]
        );
        $user->syncRoles(['SuperAdmin']);
    }
}
