<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetInfo;
use App\Models\Building;
use App\Models\Category;
use App\Models\Department;
use App\Models\Feature;
use App\Models\Level;
use App\Models\Location;
use App\Models\Room;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ErbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assetsFeature = Feature::firstOrCreate(
            ['key' => 'assets'],
            ['name' => 'Asset Management']
        );

        $departments = collect([
            [
                'name' => 'Administration',
                'code' => 'ADMIN',
                'description' => 'Central administration department.',
            ],
            [
                'name' => 'Workshops & Labs',
                'code' => 'LABS',
                'description' => 'Workshops and laboratories department.',
            ],
        ])->map(function ($data) {
            return Department::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        });

        $departments->each(function (Department $department) use ($assetsFeature) {
            $department->features()->syncWithoutDetaching([
                $assetsFeature->id => ['is_enabled' => true],
            ]);
        });

        $mainLocation = Location::firstOrCreate([
            'name' => 'Main Campus',
        ], [
            'description' => 'Primary university campus.',
        ]);
        $southLocation = Location::firstOrCreate([
            'name' => 'South Campus',
        ], [
            'description' => 'South campus facilities.',
        ]);

        $mainBuilding = Building::updateOrCreate([
            'location_id' => $mainLocation->id,
            'name' => 'Workshops Building',
        ], [
            'code' => 'WB-01',
        ]);
        $southBuilding = Building::updateOrCreate([
            'location_id' => $southLocation->id,
            'name' => 'Administration Building',
        ], [
            'code' => 'AB-01',
        ]);

        $mainLevelOne = Level::updateOrCreate([
            'building_id' => $mainBuilding->id,
            'name' => 'First Floor',
        ], [
            'level_number' => 1,
        ]);
        $mainLevelTwo = Level::updateOrCreate([
            'building_id' => $mainBuilding->id,
            'name' => 'Second Floor',
        ], [
            'level_number' => 2,
        ]);
        $southLevelOne = Level::updateOrCreate([
            'building_id' => $southBuilding->id,
            'name' => 'Ground Floor',
        ], [
            'level_number' => 0,
        ]);

        $room101 = Room::updateOrCreate([
            'level_id' => $mainLevelOne->id,
            'name' => 'Hall 101',
        ], [
            'code' => '101',
        ]);
        $room102 = Room::updateOrCreate([
            'level_id' => $mainLevelOne->id,
            'name' => 'Hall 102',
        ], [
            'code' => '102',
        ]);
        $room201 = Room::updateOrCreate([
            'level_id' => $mainLevelTwo->id,
            'name' => 'Lab 201',
        ], [
            'code' => '201',
        ]);
        $roomA1 = Room::updateOrCreate([
            'level_id' => $southLevelOne->id,
            'name' => 'Admin Office 1',
        ], [
            'code' => 'A-01',
        ]);

        $furnitureCategory = Category::firstOrCreate(['name' => 'Furniture']);
        $electronicsCategory = Category::firstOrCreate(['name' => 'Electronics']);

        $tableSubCategory = SubCategory::firstOrCreate([
            'category_id' => $furnitureCategory->id,
            'name' => 'Tables',
        ]);
        $chairSubCategory = SubCategory::firstOrCreate([
            'category_id' => $furnitureCategory->id,
            'name' => 'Chairs',
        ]);
        $computerSubCategory = SubCategory::firstOrCreate([
            'category_id' => $electronicsCategory->id,
            'name' => 'Computers',
        ]);

        $assetOne = Asset::firstOrCreate([
            'department_id' => $departments[1]->id,
            'room_id' => $room101->id,
            'sub_category_id' => $tableSubCategory->id,
        ], [
            'note' => 'Workshop tables for student projects.',
        ]);
        $assetTwo = Asset::firstOrCreate([
            'department_id' => $departments[1]->id,
            'room_id' => $room201->id,
            'sub_category_id' => $computerSubCategory->id,
        ], [
            'note' => 'High performance lab computers.',
        ]);
        $assetThree = Asset::firstOrCreate([
            'department_id' => $departments[0]->id,
            'room_id' => $roomA1->id,
            'sub_category_id' => $chairSubCategory->id,
        ], [
            'note' => 'Executive chairs.',
        ]);

        AssetInfo::firstOrCreate([
            'asset_id' => $assetOne->id,
            'key' => 'condition',
        ], [
            'value' => 'Good',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assetOne->id,
            'key' => 'material',
        ], [
            'value' => 'Steel + wood',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assetTwo->id,
            'key' => 'ip',
        ], [
            'value' => '192.168.10.25',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assetTwo->id,
            'key' => 'mac',
        ], [
            'value' => '00-14-22-01-23-45',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assetThree->id,
            'key' => 'color',
        ], [
            'value' => 'Black',
        ]);

        $superAdminRole = Role::where('name', 'SuperAdmin')->firstOrFail();
        $managerRole = Role::where('name', 'DepartmentManager')->firstOrFail();
        $staffRole = Role::where('name', 'DepartmentStaff')->firstOrFail();

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $superAdmin->assignRole($superAdminRole);
        $superAdmin->departments()->sync([
            $departments[0]->id => ['is_default' => true, 'role_id' => $superAdminRole->id],
            $departments[1]->id => ['is_default' => false, 'role_id' => $superAdminRole->id],
        ]);

        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'Department Manager', 'password' => Hash::make('password')]
        );
        $manager->assignRole($managerRole);
        $manager->departments()->sync([
            $departments[1]->id => ['is_default' => true, 'role_id' => $managerRole->id],
        ]);

        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Department Staff', 'password' => Hash::make('password')]
        );
        $staff->assignRole($staffRole);
        $staff->departments()->sync([
            $departments[0]->id => ['is_default' => false, 'role_id' => $staffRole->id],
            $departments[1]->id => ['is_default' => true, 'role_id' => $staffRole->id],
        ]);
    }
}
