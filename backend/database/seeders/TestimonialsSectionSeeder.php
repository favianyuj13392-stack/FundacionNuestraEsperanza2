<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestimonialsSection;

class TestimonialsSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['identifier' => 'testimonials', 'name' => 'Testimonios', 'order' => 1],
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
