<?php

namespace database\seeders;

use App\Models\AssetGroupType;
use Illuminate\Database\Seeder;

class AssetGroupTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'IT Bundle',
                'description' => 'A set containing a computer and its peripherals (Monitor, Keyboard, Mouse, etc.)',
                'icon' => 'desktop-computer'
            ],
            [
                'name' => 'Lab Setup',
                'description' => 'A collection of scientific instruments and furniture for specialized labs.',
                'icon' => 'beaker'
            ],
            [
                'name' => 'Office Pack',
                'description' => 'A set of desk, chair, and basic office supplies assigned to a staff member.',
                'icon' => 'briefcase'
            ],
            [
                'name' => 'Network System',
                'description' => 'A group of interconnected assets like routers, switches, and patch panels.',
                'icon' => 'server'
            ],
            [
                'name' => 'Custom Collection',
                'description' => 'A user-defined grouping of related assets.',
                'icon' => 'collection'
            ],
        ];

        foreach ($types as $type) {
            AssetGroupType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
