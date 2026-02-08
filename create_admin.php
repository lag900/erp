<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create SuperAdmin user
$user = User::updateOrCreate(
    ['email' => 'abdozero2030@gmail.com'],
    [
        'name' => 'Super Admin',
        'password' => Hash::make('1234'),
        'email_verified_at' => now()
    ]
);

// Assign SuperAdmin role
$user->assignRole('SuperAdmin');

echo "✅ SuperAdmin created successfully!\n";
echo "Email: abdozero2030@gmail.com\n";
echo "Password: 1234\n";
