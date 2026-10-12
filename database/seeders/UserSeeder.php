<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * City officials, two barangay administrations and six residents.
     *
     * Every account signs in with the password: password
     *
     * Four tiers are represented so the barangay scoping is demonstrable:
     *
     *   city.admin@barangay.gov   city_admin  sees all 18 barangays
     *   admin@barangay.gov        admin       Tagapo only
     *   staff@barangay.gov        staff       Tagapo only
     *   admin.labas@barangay.gov  admin       Labas only — same role as
     *                                        admin@barangay.gov but a different
     *                                        barangay, so signing in as each
     *                                        one proves the isolation works
     *
     * The six residents sit in six different barangays, so the freedom wall,
     * announcements and job listings visibly change between accounts.
     */
    private const BARANGAYS = [
        'admin@barangay.gov' => 'Tagapo',
        'staff@barangay.gov' => 'Tagapo',
        'admin.labas@barangay.gov' => 'Labas',
        'maria.santos@residents.ph' => 'Kanluran',
        'juan.reyes@residents.ph' => 'Pooc',
        'rosalie.mendoza@residents.ph' => 'Macabling',
        'ferdinand.aguilar@residents.ph' => 'Don Jose',
        'cristina.villanueva@residents.ph' => 'Pulong Santa Cruz',
        'emmanuel.ramos@residents.ph' => 'Sinalhan',
        'rodelyn.sarmiento@residents.ph' => 'Tagapo',
        'arnel.mercado@residents.ph' => 'Tagapo',
        'jasmine.lim@residents.ph' => 'Tagapo',
        'nestor.baldemor@residents.ph' => 'Labas',
        'marites.dizon@residents.ph' => 'Labas',
    ];

    public function run(): void
    {
        $ids = Barangay::query()->pluck('id', 'name');

        $people = [
            [
                'name'       => 'Celeste A. Ocampo',
                'email'      => 'city.admin@barangay.gov',
                'role'       => 'city_admin',
                'status'     => 'active',
                'phone'      => '0917 555 0100',
                'purok'      => null,
                'address'    => 'City Hall Complex, Rizal Boulevard, Poblacion',
                'birth_date' => '1975-05-09',
                'bio'        => 'City Administrator for the Santa Rosa e-Services rollout. Oversees all 18 barangays.',
            ],
            [
                'name'       => 'Hon. Rodolfo M. Dela Cruz',
                'email'      => 'admin@barangay.gov',
                'role'       => 'admin',
                'status'     => 'active',
                'phone'      => '0917 555 0101',
                'purok'      => 'Purok 4',
                'address'    => '12 Sampaguita Street, Zone 4',
                'birth_date' => '1968-03-14',
                'bio'        => 'Punong Barangay. Champion of a paperless, faster barangay for every Santa Rosa resident.',
            ],
            [
                'name'       => 'Angela R. Bautista',
                'email'      => 'staff@barangay.gov',
                'role'       => 'staff',
                'status'     => 'active',
                'phone'      => '0917 555 0102',
                'purok'      => 'Purok 2',
                'address'    => '45 Mabini Street, Zone 2',
                'birth_date' => '1992-07-22',
                'bio'        => 'Barangay Secretary. Processes certificates, appointments and queue windows.',
            ],
            [
                'name'       => 'Hon. Elmer P. Villareal',
                'email'      => 'admin.labas@barangay.gov',
                'role'       => 'admin',
                'status'     => 'active',
                'phone'      => '0917 555 0103',
                'purok'      => 'Purok 3',
                'address'    => '9 Narra Street, Zone 3',
                'birth_date' => '1971-09-28',
                'bio'        => 'Punong Barangay of Labas. Signing in here must show only Labas records — the Tagapo data has no business on this screen.',
            ],

            [
                'name'       => 'Maria L. Santos',
                'email'      => 'maria.santos@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4471',
                'purok'      => 'Purok 1',
                'address'    => '8 Ilang-Ilang Street, Zone 1',
                'birth_date' => '1995-11-03',
                'bio'        => 'Resident, volunteer at the city health center nutrition drive.',
            ],
            [
                'name'       => 'Juan D. Reyes',
                'email'      => 'juan.reyes@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4472',
                'purok'      => 'Purok 2',
                'address'    => '21 Bonifacio Street, Zone 2',
                'birth_date' => '1987-01-19',
                'bio'        => 'Tricycle driver and purok tanod volunteer.',
            ],
            [
                'name'       => 'Rosalie P. Mendoza',
                'email'      => 'rosalie.mendoza@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4473',
                'purok'      => 'Purok 3',
                'address'    => '3 Narra Avenue, Zone 3',
                'birth_date' => '1999-06-08',
                'bio'        => 'Online seller, member of the city livelihood association.',
            ],
            [
                'name'       => 'Ferdinand C. Aguilar',
                'email'      => 'ferdinand.aguilar@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4474',
                'purok'      => 'Purok 4',
                'address'    => '77 Kalachuchi Street, Zone 4',
                'birth_date' => '1979-09-30',
                'bio'        => 'Retired carpenter, barangay infrastructure committee member.',
            ],
            [
                'name'       => 'Cristina B. Villanueva',
                'email'      => 'cristina.villanueva@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4475',
                'purok'      => 'Purok 5',
                'address'    => '14 Acacia Road, Zone 5',
                'birth_date' => '2001-02-27',
                'bio'        => 'Working student and SK youth volunteer.',
            ],
            [
                'name'       => 'Emmanuel T. Ramos',
                'email'      => 'emmanuel.ramos@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4476',
                'purok'      => 'Purok 6',
                'address'    => '60 Molave Street, Zone 6',
                'birth_date' => '1990-12-12',
                'bio'        => 'Mechanic, weekend basketball league coordinator.',
            ],

            /* Tagapo — the barangay the two staff accounts belong to, so the
               demo admin and secretary open their dashboard onto real work
               rather than an empty board. */
            [
                'name'       => 'Rodelyn P. Sarmiento',
                'email'      => 'rodelyn.sarmiento@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4477',
                'purok'      => 'Purok 4',
                'address'    => '12 Sampaguita Street, Zone 4',
                'birth_date' => '1988-04-19',
                'bio'        => 'Sari-sari store owner near the Tagapo covered court.',
            ],
            [
                'name'       => 'Arnel B. Mercado',
                'email'      => 'arnel.mercado@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4478',
                'purok'      => 'Purok 2',
                'address'    => '45 Mabini Street, Zone 2',
                'birth_date' => '1979-08-05',
                'bio'        => 'Tricycle driver; reports drainage and streetlight issues along the route.',
            ],
            [
                'name'       => 'Jasmine D. Lim',
                'email'      => 'jasmine.lim@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4479',
                'purok'      => 'Purok 5',
                'address'    => '88 Ilang-Ilang Street, Zone 5',
                'birth_date' => '2001-02-27',
                'bio'        => 'College student and SK volunteer; files requests for scholarship paperwork.',
            ],

            /* Labas — gives the second barangay admin a board with work on it
               rather than a screen of zeros next to Tagapo's populated one. */
            [
                'name'       => 'Nestor P. Baldemor',
                'email'      => 'nestor.baldemor@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4480',
                'purok'      => 'Purok 1',
                'address'    => '31 Narra Street, Zone 1',
                'birth_date' => '1966-06-30',
                'bio'        => 'Barangay tanod; requests residency certificates for scholarship applicants.',
            ],
            [
                'name'       => 'Marites A. Dizon',
                'email'      => 'marites.dizon@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4481',
                'purok'      => 'Purok 3',
                'address'    => '17 Mabini Street, Zone 3',
                'birth_date' => '1985-01-14',
                'bio'        => 'Sells at the Labas market; books appointments for business clearances.',
            ],
        ];

        foreach ($people as $person) {
            $barangay = self::BARANGAYS[$person['email']] ?? null;

            // A named barangay must exist; a miss is a seeder bug, not a
            // silent null that would leave the account seeing nothing.
            if ($barangay !== null && ! isset($ids[$barangay])) {
                $this->command?->error("Unknown barangay for {$person['email']}: {$barangay}");

                continue;
            }

            User::query()->firstOrCreate(
                ['email' => $person['email']],
                $person + [
                    'password' => 'password',
                    'barangay_id' => $barangay !== null ? $ids[$barangay] : null,
                ]
            );
        }
    }
}
