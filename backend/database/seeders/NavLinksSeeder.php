<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NavLink;

class NavLinksSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            ['title' => 'Inicio', 'url' => '/', 'location' => 'both', 'order' => 1],
            ['title' => 'Quiénes Somos', 'url' => '/quienes-somos', 'location' => 'both', 'order' => 2],
            ['title' => 'Programas', 'url' => '/programas', 'location' => 'both', 'order' => 3],
            ['title' => 'Cómo Ayudar', 'url' => '/como-ayudar', 'location' => 'both', 'order' => 4],
            ['title' => 'Noticias', 'url' => '/news', 'location' => 'both', 'order' => 5],
            ['title' => 'Contacto', 'url' => '/#contacto', 'location' => 'header', 'order' => 6],
            ['title' => 'Anuncios', 'url' => '/anuncios', 'location' => 'both', 'order' => 7],
        ];

        foreach ($links as $link) {
            // updateOrCreate evita que se dupliquen si corres el seeder varias veces
            NavLink::updateOrCreate(
                ['title' => $link['title']], 
                $link
            );
        }
    }
}