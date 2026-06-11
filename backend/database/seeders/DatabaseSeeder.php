<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
            TestUserSeeder::class,
            SettingsSeeder::class,
            NavLinksSeeder::class,
            StatSeeder::class,
            HomeSectionSeeder::class,
            AboutUsSectionSeeder::class,
            ProgramsSectionSeeder::class,
            TestimonialsSectionSeeder::class,
            NewsSectionSeeder::class,
            HowToHelpSectionSeeder::class,
            AllianceSeeder::class,
            AdvertisementSeeder::class,
            DataImportSeeder::class,
        ]);

        // Asegurar la existencia de monedas básicas
        \Illuminate\Support\Facades\DB::table('currencies')->insertOrIgnore([
            ['id' => 1, 'name' => 'Dólar Estadounidense', 'iso_code' => 'USD', 'symbol' => '$'],
            ['id' => 2, 'name' => 'Boliviano', 'iso_code' => 'BOB', 'symbol' => 'Bs.'],
        ]);
    }
}

