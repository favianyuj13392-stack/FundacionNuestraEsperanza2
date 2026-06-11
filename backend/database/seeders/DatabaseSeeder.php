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
    }
}
