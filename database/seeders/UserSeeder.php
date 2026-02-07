<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'abdozero2030@gmail.com'],
            [
                'name' => 'Abdelrhman Ibrahim Sayed',
                'password' => \Illuminate\Support\Facades\Hash::make('1234'),
            ]
        );

        $user->assignRole('SuperAdmin');
    }
}
