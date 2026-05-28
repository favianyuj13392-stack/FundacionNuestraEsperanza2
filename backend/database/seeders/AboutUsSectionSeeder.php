<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutUsSection;

class AboutUsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'about_us', 'name' => 'Quiénes Somos', 'order' => 1],
        ];

        foreach ($sections as $section) {
            AboutUsSection::updateOrCreate(
                ['identifier' => $section['identifier']],
                [
                    'name' => $section['name'],
                    'order' => $section['order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
