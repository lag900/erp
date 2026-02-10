<?php

use App\Models\User;
use App\Models\Department;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = User::with(['roles', 'departments'])->get();

echo "--- User List ---\n";
foreach ($users as $user) {
    /** @var User $user */
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Departments: " . $user->departments->pluck('name')->implode(', ') . "\n";
    echo "------------------\n";
    
    // Check if this user is a potential candidate for Super Admin
    if ($user->hasRole('SuperAdmin') && $user->departments->contains('code', 'ADMIN')) {
         echo "FOUND: Check above user credentials.\n";
    }
}

// If no user found, create one
$adminDept = Department::where('code', 'ADMIN')->first();
if (!$adminDept) {
    echo "Creating Admin Dept...\n";
    $adminDept = Department::create([
        'code' => 'ADMIN',
        'name' => 'Administration',
        'name_ar' => 'الإدارة العامة'
    ]);
}

$superAdmin = User::where('email', 'admin@example.com')->first();
if (!$superAdmin) {
    echo "Creating Super Admin user...\n";
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $superAdmin->assignRole('SuperAdmin');
    $superAdmin->departments()->attach($adminDept->id);
    echo "Created user: admin@example.com / password\n";
} else {
    // Ensure roles and department
    if (!$superAdmin->hasRole('SuperAdmin')) {
        $superAdmin->assignRole('SuperAdmin');
        echo "Assigned SuperAdmin role to existing user.\n";
    }
    if (!$superAdmin->departments->contains($adminDept->id)) {
        $superAdmin->departments()->attach($adminDept->id);
        echo "Attached Admin department to existing user.\n";
    }
    echo "Use existing user: admin@example.com / password (if unchanged)\n";
}
