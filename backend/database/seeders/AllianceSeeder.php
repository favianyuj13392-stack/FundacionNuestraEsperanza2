<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AllianceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alliances = [
            ['name' => 'Red Alianza Latina', 'url' => 'https://redalianzalatina.org/es/home-esp/'],
            ['name' => 'Childhood Cancer Support', 'url' => 'https://www.childhood-cancer-support.com/'],
            ['name' => 'Canica', 'url' => 'https://canica.org.mx/'],
            ['name' => 'St. Jude', 'url' => 'https://www.stjude.org/es/'],
            ['name' => 'My Child Matters', 'url' => 'https://foundation-s-mychildmatters.opendatasoft.com/pages/home/'],
        ];

        foreach ($alliances as $alliance) {
            \App\Models\Alliance::updateOrCreate(
                ['name' => $alliance['name']],
                [
                    'url' => $alliance['url'],
                    'is_active' => true,
                    // 'logo' => null, // El admin deberá subir el logo desde la biblioteca RF-05
                ]
            );
        }
    }
}
