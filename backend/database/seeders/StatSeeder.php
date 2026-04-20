<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stat;

class StatSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            ['label' => 'Niños Ayudados', 'number' => 1500],
            ['label' => 'Voluntarios Activos', 'number' => 320],
            ['label' => 'Proyectos Completados', 'number' => 45],
        ];

        foreach ($stats as $stat) {
            Stat::firstOrCreate(['label' => $stat['label']], $stat);
        }
    }
}