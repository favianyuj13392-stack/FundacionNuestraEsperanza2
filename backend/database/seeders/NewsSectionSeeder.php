<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsSection;

class NewsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'news', 'name' => 'Últimas Noticias', 'order' => 1],
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
