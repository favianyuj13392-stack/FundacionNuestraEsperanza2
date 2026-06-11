<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestimonialsSection;

class TestimonialsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'testimonials_hero', 'name' => 'Hero de Testimonios', 'order' => 1],
            ['identifier' => 'testimonials_section', 'name' => 'Sección de Testimonios', 'order' => 2],
            ['identifier' => 'quotes', 'name' => 'Citas Inspiradoras', 'order' => 3],
            ['identifier' => 'alliances', 'name' => 'Alianzas', 'order' => 4],
        ];

        foreach ($sections as $section) {
            TestimonialsSection::updateOrCreate(
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
