<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramsSection;

class ProgramsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'programs_hero', 'name' => 'Hero de Programas', 'order' => 1],
            ['identifier' => 'programs_section', 'name' => 'Sección de Programas', 'order' => 2],
            ['identifier' => 'how_to_help', 'name' => 'Cómo Ayudar', 'order' => 3],
            ['identifier' => 'alliances', 'name' => 'Alianzas', 'order' => 4],
            ['identifier' => 'subscribe', 'name' => 'Suscripción Newsletter', 'order' => 5],
            ['identifier' => 'contact', 'name' => 'Sección de Contacto', 'order' => 6],
        ];

        foreach ($sections as $section) {
            ProgramsSection::updateOrCreate(
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
