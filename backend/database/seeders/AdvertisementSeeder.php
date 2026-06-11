<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Advertisement;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $advertisements = [
            [
                'title' => 'Campaña de Recaudación: Útiles Escolares 2024',
                'slug' => 'campana-recaudacion-utiles-escolares-2024',
                'content' => '<p>Estamos recolectando cuadernos y mochilas para los niños del albergue.</p>',
                'is_active' => true,
                'order' => 1,
                'image' => null,
            ],
            [
                'title' => 'Taller de Psicología para Padres',
                'slug' => 'taller-psicologia-padres',
                'content' => '<p>Únete a nuestra charla mensual sobre salud emocional en la familia.</p>',
                'is_active' => true,
                'order' => 2,
                'image' => null,
            ],
        ];

        foreach ($advertisements as $ad) {
            Advertisement::updateOrCreate(
                ['slug' => $ad['slug']],
                $ad
            );
        }
    }
}