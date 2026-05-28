<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsSection;

class NewsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'news_hero', 'name' => 'Hero de Noticias', 'order' => 1],
            ['identifier' => 'news_section', 'name' => 'Sección de Noticias', 'order' => 2],
            ['identifier' => 'alliances', 'name' => 'Alianzas', 'order' => 3],
        ];

        foreach ($sections as $section) {
            NewsSection::updateOrCreate(
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
