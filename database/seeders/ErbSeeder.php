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
        $assetsFeature = $this->seedFeatures();
        $departments = $this->seedDepartments($assetsFeature);
        $locations = $this->seedLocations();
        $buildings = $this->seedBuildings($locations);
        $levels = $this->seedLevels($buildings);
        $rooms = $this->seedRooms($levels);
        $categories = $this->seedCategories();
        $subCategories = $this->seedSubCategories($categories);
        $assets = $this->seedAssets($departments, $rooms, $subCategories);
        $this->seedAssetInfo($assets);
        $this->seedUsers($departments);
    }

    /**
     * Seed features.
     */
    private function seedFeatures(): Feature
    {
        return Feature::firstOrCreate(
            ['key' => 'assets'],
            ['name' => 'Asset Management']
        );
    }

    /**
     * Seed departments and attach features.
     */
    private function seedDepartments(Feature $assetsFeature): \Illuminate\Support\Collection
    {
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

        return $departments;
    }

    /**
     * Seed locations.
     */
    private function seedLocations(): array
    {
        return [
            'main' => Location::firstOrCreate([
                'name' => 'Main Campus',
            ], [
                'description' => 'Primary university campus.',
            ]),
            'south' => Location::firstOrCreate([
                'name' => 'South Campus',
            ], [
                'description' => 'South campus facilities.',
            ]),
        ];
    }

    /**
     * Seed buildings.
     */
    private function seedBuildings(array $locations): array
    {
        return [
            'main' => Building::updateOrCreate([
                'location_id' => $locations['main']->id,
                'name_en' => 'Workshops Building',
            ], [
                'name' => 'Workshops Building',
                'name_ar' => 'مبنى الورش والمعامل',
                'code' => 'WB-01',
            ]),
            'south' => Building::updateOrCreate([
                'location_id' => $locations['south']->id,
                'name_en' => 'Administration Building',
            ], [
                'name' => 'Administration Building',
                'name_ar' => 'مبنى الإدارة المركزية',
                'code' => 'AB-01',
            ]),
        ];
    }

    /**
     * Seed levels.
     */
    private function seedLevels(array $buildings): array
    {
        return [
            'main_1' => Level::updateOrCreate([
                'building_id' => $buildings['main']->id,
                'name' => 'First Floor',
            ], [
                'level_number' => 1,
            ]),
            'main_2' => Level::updateOrCreate([
                'building_id' => $buildings['main']->id,
                'name' => 'Second Floor',
            ], [
                'level_number' => 2,
            ]),
            'south_0' => Level::updateOrCreate([
                'building_id' => $buildings['south']->id,
                'name' => 'Ground Floor',
            ], [
                'level_number' => 0,
            ]),
        ];
    }

    /**
     * Seed rooms.
     */
    private function seedRooms(array $levels): array
    {
        return [
            '101' => Room::updateOrCreate([
                'level_id' => $levels['main_1']->id,
                'name' => 'Hall 101',
            ], [
                'code' => '101',
            ]),
            '102' => Room::updateOrCreate([
                'level_id' => $levels['main_1']->id,
                'name' => 'Hall 102',
            ], [
                'code' => '102',
            ]),
            '201' => Room::updateOrCreate([
                'level_id' => $levels['main_2']->id,
                'name' => 'Lab 201',
            ], [
                'code' => '201',
            ]),
            'A1' => Room::updateOrCreate([
                'level_id' => $levels['south_0']->id,
                'name' => 'Admin Office 1',
            ], [
                'code' => 'A-01',
            ]),
        ];
    }

    /**
     * Seed categories.
     */
    private function seedCategories(): array
    {
        return [
            'furniture' => Category::firstOrCreate(['name' => 'Furniture']),
            'electronics' => Category::firstOrCreate(['name' => 'Electronics']),
        ];
    }

    /**
     * Seed sub-categories.
     */
    private function seedSubCategories(array $categories): array
    {
        return [
            'tables' => SubCategory::firstOrCreate([
                'category_id' => $categories['furniture']->id,
                'name' => 'Tables',
            ]),
            'chairs' => SubCategory::firstOrCreate([
                'category_id' => $categories['furniture']->id,
                'name' => 'Chairs',
            ]),
            'computers' => SubCategory::firstOrCreate([
                'category_id' => $categories['electronics']->id,
                'name' => 'Computers',
            ]),
        ];
    }

    /**
     * Seed assets.
     */
    private function seedAssets(
        \Illuminate\Support\Collection $departments,
        array $rooms,
        array $subCategories
    ): array {
        return [
            'one' => Asset::firstOrCreate([
                'department_id' => $departments[1]->id,
                'room_id' => $rooms['101']->id,
                'sub_category_id' => $subCategories['tables']->id,
            ], [
                'note' => 'Workshop tables for student projects.',
            ]),
            'two' => Asset::firstOrCreate([
                'department_id' => $departments[1]->id,
                'room_id' => $rooms['201']->id,
                'sub_category_id' => $subCategories['computers']->id,
            ], [
                'note' => 'High performance lab computers.',
            ]),
            'three' => Asset::firstOrCreate([
                'department_id' => $departments[0]->id,
                'room_id' => $rooms['A1']->id,
                'sub_category_id' => $subCategories['chairs']->id,
            ], [
                'note' => 'Executive chairs.',
            ]),
        ];
    }

    /**
     * Seed asset information.
     */
    private function seedAssetInfo(array $assets): void
    {
        AssetInfo::firstOrCreate([
            'asset_id' => $assets['one']->id,
            'key' => 'condition',
        ], [
            'value' => 'Good',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assets['one']->id,
            'key' => 'material',
        ], [
            'value' => 'Steel + wood',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assets['two']->id,
            'key' => 'ip',
        ], [
            'value' => '192.168.10.25',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assets['two']->id,
            'key' => 'mac',
        ], [
            'value' => '00-14-22-01-23-45',
        ]);
        AssetInfo::firstOrCreate([
            'asset_id' => $assets['three']->id,
            'key' => 'color',
        ], [
            'value' => 'Black',
        ]);
    }

    /**
     * Seed users with roles and departments.
     */
    private function seedUsers(\Illuminate\Support\Collection $departments): void
    {
        $superAdminRole = Role::where('name', 'SuperAdmin')->firstOrFail();
        $managerRole = Role::where('name', 'Manager')->firstOrFail();
        $staffRole = Role::where('name', 'Data Entry')->firstOrFail();

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $superAdmin->assignRole($superAdminRole);
        $superAdmin->departments()->sync([
            $departments[0]->id => ['is_default' => true, 'role_id' => $superAdminRole->id],
            $departments[1]->id => ['is_default' => false, 'role_id' => $superAdminRole->id],
        ]);

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'Department Manager', 'password' => Hash::make('password')]
        );
        $manager->assignRole($managerRole);
        $manager->departments()->sync([
            $departments[1]->id => ['is_default' => true, 'role_id' => $managerRole->id],
        ]);

        // Staff
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
