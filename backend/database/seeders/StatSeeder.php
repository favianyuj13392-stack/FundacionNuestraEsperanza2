<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stat;

class StatSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            ['label' => 'Niños que Recibieron Ayuda', 'number' => 250],
            ['label' => 'Diagnósticos de Niños con Cáncer por Año en Bolivia', 'number' => 430],
            ['label' => 'Voluntarios', 'number' => 20],
        ];

        foreach ($stats as $stat) {
            Stat::firstOrCreate(['label' => $stat['label']], $stat);
        }
    }
}