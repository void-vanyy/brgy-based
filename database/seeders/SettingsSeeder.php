<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * City profile shown across the public site, portals and printouts.
     *
     * The keys keep their original barangay_* names because the views, the
     * settings form and every printed document already read them; only the
     * values change now that one portal serves all 18 barangays.
     */
    public function run(): void
    {
        $settings = [
            // No "e-Services" suffix here: home.blade.php and features.blade.php
            // both append it themselves, so a suffixed value reads twice over.
            'barangay_name'    => 'Santa Rosa City',
            'barangay_address' => 'City Hall Complex, Rizal Boulevard, Poblacion, Santa Rosa City, Laguna 4026',
            'barangay_contact' => '(049) 534-3000 / eservices@santrosacity.gov.ph',
            'barangay_capain'  => 'Hon. Celeste A. Ocampo, City Administrator',
            'barangay_motto'   => 'Makabuluhan, Mapagkalinga, Magkaisa.',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
