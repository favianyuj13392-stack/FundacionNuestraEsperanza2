<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeSection;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'hero', 'name' => 'Banner Principal (Hero)', 'order' => 1],
            ['identifier' => 'stats', 'name' => 'Estadísticas', 'order' => 2],
            ['identifier' => 'about_us', 'name' => 'Quiénes Somos', 'order' => 3],
            ['identifier' => 'programs', 'name' => 'Nuestros Programas', 'order' => 4],
            ['identifier' => 'how_to_help', 'name' => 'Cómo Ayudar', 'order' => 5],
            ['identifier' => 'testimonials', 'name' => 'Testimonios', 'order' => 6],
            ['identifier' => 'news', 'name' => 'Últimas Noticias', 'order' => 7],
            ['identifier' => 'contact', 'name' => 'Sección de Contacto', 'order' => 8],
            ['identifier' => 'subscribe', 'name' => 'Suscripción Newsletter', 'order' => 9],
            ['identifier' => 'alliances', 'name' => 'Nuestras Alianzas', 'order' => 10],
        ];

        foreach ($sections as $section) {
            HomeSection::updateOrCreate(
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