<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure Administration department exists
        $adminDept = Department::where('code', 'ADMIN')->first();
        if (!$adminDept) {
            $adminId = DB::table('departments')->insertGetId([
                'name' => 'Administration',
                'code' => 'ADMIN',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $adminId = $adminDept->id;
        }

        // 2. List of tables that should be owned by Administration if orphaned
        $tables = [
            'locations',
            'buildings',
            'levels',
            'rooms',
            'categories',
            'sub_categories',
            'news',
            'assets'
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'department_id')) {
                $count = DB::table($table)->whereNull('department_id')->count();
                if ($count > 0) {
                    echo "Assigning $count orphaned records in $table to Administration...\n";
                    DB::table($table)->whereNull('department_id')->update(['department_id' => $adminId]);
                }
            }
        }

        // 3. Category Visibility: Ensure all currently existing categories are shared with Administration
        $categories = DB::table('categories')->get();
        foreach ($categories as $category) {
            $exists = DB::table('category_department')
                ->where('category_id', $category->id)
                ->where('department_id', $adminId)
                ->exists();
            if (!$exists) {
                DB::table('category_department')->insert([
                    'category_id' => $category->id,
                    'department_id' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Building Visibility: Ensure all currently existing buildings are shared with Administration
        $buildings = DB::table('buildings')->get();
        foreach ($buildings as $building) {
            $exists = DB::table('building_department')
                ->where('building_id', $building->id)
                ->where('department_id', $adminId)
                ->exists();
            if (!$exists) {
                DB::table('building_department')->insert([
                    'building_id' => $building->id,
                    'department_id' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "Legacy data migration complete. All historical records are now owned by Administration.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration for structural integrity.
    }
};
