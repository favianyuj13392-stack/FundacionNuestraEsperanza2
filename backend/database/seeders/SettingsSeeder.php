<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // --- CONTACTO (Para Navbar y Footer) (RF-02) ---
            ['key' => 'contact_whatsapp', 'value' => '+59170000000', 'type' => 'text', 'group' => 'Contacto'],
            ['key' => 'contact_phone', 'value' => '(591) 2 2222222', 'type' => 'text', 'group' => 'Contacto'],
            ['key' => 'contact_email', 'value' => 'info@nuestraesperanza.org', 'type' => 'text', 'group' => 'Contacto'],
            ['key' => 'contact_address', 'value' => 'Av. Arce #2525, La Paz, Bolivia', 'type' => 'text', 'group' => 'Contacto'],

            // --- ESTADÍSTICAS (Para el componente Stats.tsx) (RF-01) ---
            ['key' => 'stats_ninos_ayudados', 'value' => '250', 'type' => 'number', 'group' => 'Estadísticas'],
            ['key' => 'stats_diagnosticos_anuales', 'value' => '430', 'type' => 'number', 'group' => 'Estadísticas'],
            ['key' => 'stats_voluntarias', 'value' => '20', 'type' => 'number', 'group' => 'Estadísticas'],

            // --- TEXTOS INSTITUCIONALES (Para Footer y Banners) (RF-01) ---
            ['key' => 'footer_about_text', 'value' => 'Brindamos un hogar cálido, alimentación y apoyo psicosocial a niños con cáncer y sus familias para evitar el abandono del tratamiento.', 'type' => 'longtext', 'group' => 'Textos Institucionales'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}