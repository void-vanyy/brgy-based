<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Two barangay staff accounts plus six residents.
     * Every account signs in with the password: password
     */
    public function run(): void
    {
        $people = [
            [
                'name'       => 'Hon. Rodolfo M. Dela Cruz',
                'email'      => 'admin@barangay.gov',
                'role'       => 'admin',
                'status'     => 'active',
                'phone'      => '0917 555 0101',
                'purok'      => 'Purok 4',
                'address'    => '12 Sampaguita Street, Zone 4',
                'birth_date' => '1968-03-14',
                'bio'        => 'Punong Barangay. Champion of a paperless, faster barangay for every Sigla resident.',
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
                'name'       => 'Maria L. Santos',
                'email'      => 'maria.santos@residents.ph',
                'role'       => 'resident',
                'status'     => 'active',
                'phone'      => '0918 220 4471',
                'purok'      => 'Purok 1',
                'address'    => '8 Ilang-Ilang Street, Zone 1',
                'birth_date' => '1995-11-03',
                'bio'        => 'Resident, volunteer at the Sigla Health Center nutrition drive.',
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
                'bio'        => 'Online seller, member of the Sigla livelihood association.',
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
        ];

        foreach ($people as $person) {
            User::query()->firstOrCreate(
                ['email' => $person['email']],
                $person + ['password' => 'password']
            );
        }
    }
}
