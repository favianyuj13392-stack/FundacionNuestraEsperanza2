<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // --- REDES SOCIALES (Para los íconos del Footer) ---
            ['key' => 'social_facebook', 'value' => 'https://www.facebook.com/NuestraEsperanzaBo/?locale=es_LA', 'type' => 'text', 'group' => 'Redes Sociales'],
            ['key' => 'social_instagram', 'value' => 'https://www.instagram.com/accounts/login/?next=%2Ffundacionnuestraesperanza&source=omni_redirect', 'type' => 'text', 'group' => 'Redes Sociales'],
            ['key' => 'social_tiktok', 'value' => 'https://www.tiktok.com/@fund.nuestra.esperanza', 'type' => 'text', 'group' => 'Redes Sociales'],

            // --- TEXTOS INSTITUCIONALES (Para la descripción corta del Footer) ---
            ['key' => 'footer_about_text', 'value' => 'Haciendo una diferencia en la vida de los niños que padecen de cáncer en toda Bolivia.', 'type' => 'longtext', 'group' => 'Textos Institucionales'],
            
            // --- CONTACTO (Solo si tienes un botón de WhatsApp general en el Navbar/Footer) ---
            ['key' => 'contact_phone', 'value' => '+59170112236', 'type' => 'text', 'group' => 'Contacto'],
            ['key' => 'contact_email', 'value' => '+59170112236', 'type' => 'text', 'group' => 'Contacto'],
            // LOGO
            ['key' => 'global_logo', 'value' => null, 'type' => 'image', 'group' => 'General'],
            ['key' => 'site_name', 'value' => 'Fundación Nuestra Esperanza', 'type' => 'text', 'group' => 'General'],
        ];

        foreach ($settings as $setting) {
            // Usamos updateOrCreate para asegurarnos de que se actualicen si ya existían
            Setting::updateOrCreate(
                ['key' => $setting['key']], 
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group']
                ]
            );
        }
    }
}
