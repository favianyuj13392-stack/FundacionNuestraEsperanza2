<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramsSection;

class ProgramsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'programs', 'name' => 'Nuestros Programas', 'order' => 1],
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
