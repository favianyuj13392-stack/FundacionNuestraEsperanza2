<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alliance;

class AllianceSeeder extends Seeder
{
    public function run(): void
    {
        $alliances = [
            ['name' => 'Red Alianza Latina', 'url' => 'https://redalianzalatina.org/es/home-esp/', 'order' => 1],
            ['name' => 'Childhood Cancer Support', 'url' => 'https://www.childhood-cancer-support.com/', 'order' => 2],
            ['name' => 'Canica', 'url' => 'https://canica.org.mx/', 'order' => 3],
            ['name' => 'St. Jude', 'url' => 'https://www.stjude.org/es/', 'order' => 4],
            ['name' => 'My Child Matters', 'url' => 'https://foundation-s-mychildmatters.opendatasoft.com/pages/home/', 'order' => 5],
        ];

        foreach ($alliances as $alliance) {
            Alliance::updateOrCreate(
                ['name' => $alliance['name']], // Busca por nombre
                [
                    'url' => $alliance['url'],
                    'sort_order' => $alliance['order'],
                    'is_active' => true,
                    'logo' => null,
                ]
            );
        }
    }
}