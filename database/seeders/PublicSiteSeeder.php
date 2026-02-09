<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\AdministrationUnit;
use App\Models\News;
use Illuminate\Database\Seeder;

class PublicSiteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clear existing public data to ensure freshness
        // Department::where('type', 'faculty')->delete(); // Be careful if there are relationships
        
        // 2. Add Faculties
        $faculties = [
            [
                'name' => 'Faculty of Industry and Energy Technology',
                'arabic_name' => 'كلية تكنولوجيا الصناعة والطاقة',
                'code' => 'FIET',
                'description' => 'Dedicated to pioneering technological solutions in energy, manufacturing, and industrial systems. Our programs include IT, Food Industry, Spinning and Weaving, and Railway Technology.',
                'director_name' => 'Prof. Dr. Alaa Arafa',
                'director_title' => 'Dean of Faculty',
                'type' => 'faculty',
                'status' => 'active',
                'display_order' => 1,
            ],
            [
                'name' => 'Faculty of Health Sciences Technology',
                'arabic_name' => 'كلية تكنولوجيا العلوم الصحية',
                'code' => 'FHST',
                'description' => 'Preparing skilled professionals in nursing technology, pharmaceutical production, and health information management. Focusing on the integration of medical science and technology.',
                'director_name' => 'Prof. Dr. Ibrahim El Fahham',
                'director_title' => 'Dean of Faculty',
                'type' => 'faculty',
                'status' => 'active',
                'display_order' => 2,
            ],
        ];

        foreach ($faculties as $faculty) {
            Department::updateOrCreate(['code' => $faculty['code']], $faculty);
        }

        // 3. Add Administration Units
        $admins = [
            [
                'title' => 'University Rector Office',
                'description' => 'The central executive body responsible for the overall strategic direction and administration of the University.',
                'director_name' => 'Prof. Dr. Mohamed El Gohary',
                'director_title' => 'University President',
                'status' => 'active',
                'display_order' => 1,
            ],
            [
                'title' => 'Academic Affairs',
                'description' => 'Managing academic programs, curriculum development, and faculty research initiatives across all departments.',
                'director_name' => 'Prof. Dr. Ahmed El-Sayed',
                'director_title' => 'Vice President for Academic Affairs',
                'status' => 'active',
                'display_order' => 2,
            ],
            [
                'title' => 'Student Affairs & Education',
                'description' => 'Dedicated to supporting student life, admissions, registration, and comprehensive student services.',
                'director_name' => 'Dr. Mona Hassan',
                'director_title' => 'Dean of Student Affairs',
                'status' => 'active',
                'display_order' => 3,
            ],
        ];

        foreach ($admins as $admin) {
            AdministrationUnit::updateOrCreate(['title' => $admin['title']], $admin);
        }

        // 4. Add some real news
        $news = [
            [
                'title' => 'BATU Explores Partnership with Fulbright Egypt',
                'description' => 'President Mohamed Morsi ElGohary welcomed a Fulbright Egypt team to discuss international academic exchange opportunities for students and staff.',
                'content' => 'In a significant move towards internationalization, Borg El Arab Technological University hosted the Fulbright Egypt team. The meeting focused on various grant programs and research opportunities in the USA, aiming to enhance the global exposure of our academic community.',
                'category' => 'Official',
                'publish_date' => now(),
                'status' => 'published',
            ],
            [
                'title' => 'New Railway Technology Center to be established at BATU',
                'description' => 'In collaboration with international partners, BATU is launching a state-of-the-art center for railway engineering and maintenance.',
                'content' => 'The university has signed a memorandum of understanding to establish an advanced railway center. This center will provide students with hands-on training using the latest technologies in transportation and infrastructure maintenance.',
                'category' => 'Partnerships',
                'publish_date' => now()->subDays(2),
                'status' => 'published',
            ],
        ];

        foreach ($news as $item) {
            News::updateOrCreate(['title' => $item['title']], $item);
        }
    }
}
