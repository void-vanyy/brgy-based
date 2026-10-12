<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;

/**
 * The 18 barangays of Santa Rosa City, Laguna.
 *
 * Sources:
 *   names + aliases  Wikipedia / philatlas.com / the official city directory
 *                    (santarosacity.gov.ph), all three in agreement
 *   coordinates      philatlas.com per-barangay profiles, each stating
 *                    "X is situated at approximately <lat>, <lng>". Cross
 *                    checked against GeoNames ADM4 centroids; every point
 *                    falls inside the same barangay in both sources.
 *   PSGC codes       PSA PSGC correspondence codes, verified against three
 *                    independent PSA-derived datasets. Note the codes are
 *                    NOT contiguous — 013, 014, 017, 019 and 021 are unused
 *                    because the old Poblacion split into Kanluran, Malusak
 *                    and Market Area.
 *   population       PSA 2020 census.
 *
 * Re-run safe: rows are keyed on the unique psgc_code.
 */
class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // name,                 alias,              psgc,       lat,       lng,        pop
            ['Aplaya', null, '043428001', 14.3151, 121.1219, 15858],
            ['Balibago', null, '043428002', 14.2946, 121.1054, 21566],
            ['Caingin', null, '043428003', 14.2985, 121.1270, 24481],
            ['Dila', null, '043428004', 14.2902, 121.1095, 39496],
            ['Dita', null, '043428005', 14.2818, 121.1115, 34650],
            ['Don Jose', null, '043428006', 14.2564, 121.0664, 18039],
            ['Ibaba', null, '043428007', 14.3146, 121.1184, 8069],
            ['Kanluran', 'Poblacion Uno', '043428015', 14.3127, 121.1103, 4236],
            ['Labas', null, '043428008', 14.3070, 121.1104, 21288],
            ['Macabling', null, '043428009', 14.2986, 121.0972, 20406],
            ['Malitlit', null, '043428010', 14.2691, 121.1111, 27433],
            ['Malusak', 'Poblacion Dos', '043428011', 14.3116, 121.1138, 2906],
            ['Market Area', 'Poblacion Tres', '043428012', 14.3167, 121.1156, 16017],
            ['Pooc', 'Pook', '043428016', 14.3000, 121.1117, 55705],
            ['Pulong Santa Cruz', null, '043428018', 14.2766, 121.0831, 29421],
            ['Santo Domingo', null, '043428020', 14.2284, 121.0479, 6863],
            ['Sinalhan', null, '043428022', 14.3297, 121.1113, 26274],
            ['Tagapo', null, '043428023', 14.3185, 121.1028, 42104],
        ];

        foreach ($rows as [$name, $alias, $psgc, $lat, $lng, $population]) {
            Barangay::query()->updateOrCreate(
                ['psgc_code' => $psgc],
                [
                    'name' => $name,
                    'alias' => $alias,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'population_2020' => $population,
                    'is_active' => true,
                ]
            );
        }
    }
}
