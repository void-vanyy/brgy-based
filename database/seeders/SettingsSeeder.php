<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Barangay profile shown across the public site, portals and printouts.
     */
    public function run(): void
    {
        $settings = [
            'barangay_name'    => 'Barangay Sigla',
            'barangay_address' => 'Sigla Street, Zone 4, District II, Philippines',
            'barangay_contact' => '(02) 8536-1188',
            'barangay_capain'  => 'Hon. Rodolfo M. Dela Cruz',
            'barangay_motto'   => 'Makabago, Mapagkalinga, Maaasahan.',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
