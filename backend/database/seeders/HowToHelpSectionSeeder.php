<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HowToHelpSection;

class HowToHelpSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'help_hero', 'name' => 'Hero de Cómo Ayudar', 'order' => 1],
            ['identifier' => 'direct_donations', 'name' => 'Donaciones Directas', 'order' => 2],
            ['identifier' => 'how_to_help', 'name' => 'Sección Cómo Ayudar', 'order' => 3],
            ['identifier' => 'more_ways_to_help', 'name' => 'Más Formas de Ayudar', 'order' => 4],
            ['identifier' => 'social_media_help', 'name' => 'Ayuda en Redes Sociales', 'order' => 5],
            ['identifier' => 'qr_donation_section', 'name' => 'Donación con QR', 'order' => 6],
            ['identifier' => 'alliances', 'name' => 'Alianzas', 'order' => 7],
        ];

        foreach ($sections as $section) {
            HowToHelpSection::updateOrCreate(
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
