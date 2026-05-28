<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutUsSection;

class AboutUsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'about_us_hero', 'name' => 'Banner Principal (Hero)', 'order' => 1],
            ['identifier' => 'about_us', 'name' => 'Quiénes Somos', 'order' => 2],
            ['identifier' => 'mission_vision', 'name' => 'Misión y Visión', 'order' => 3],
            ['identifier' => 'our_programs', 'name' => 'Nuestros Pilares', 'order' => 4],
            ['identifier' => 'team_directory', 'name' => 'Nuestro Directorio', 'order' => 5],
            ['identifier' => 'quotes', 'name' => 'Citas Inspiradoras', 'order' => 6],
            ['identifier' => 'subscribe', 'name' => 'Suscripción Newsletter', 'order' => 7],
        ];

        foreach ($sections as $section) {
            AboutUsSection::updateOrCreate(
                ['identifier' => $section['identifier']],
                [
                    'name' => $section['name'],
                    'order' => $section['order'],
                    'is_active' => true, // Por defecto todas activas
                ]
            );
        }
    }
}
