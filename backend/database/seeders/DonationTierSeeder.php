<?php

namespace Database\Seeders;

use App\Models\DonationTier;
use Illuminate\Database\Seeder;

class DonationTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'amount' => 20,
                'label' => 'Apoyo Escolar',
                'currency_id' => 2, // Assuming 2 is BOB based on previous context or migration default
                'is_active' => true,
                'order' => 1,
            ],
            [
                'amount' => 50,
                'label' => 'Canasta Familiar',
                'currency_id' => 2,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'amount' => 100,
                'label' => 'Salud Integral',
                'currency_id' => 2,
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($tiers as $tier) {
            DonationTier::create($tier);
        }
    }
}
