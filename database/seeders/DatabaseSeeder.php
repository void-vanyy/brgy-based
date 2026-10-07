<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Deterministic demo data for the whole application.
     *
     * Order matters: settings and users are referenced by every later seeder.
     * Every seeder keys its rows on a unique column, so `db:seed` can be
     * re-run safely without duplicating records.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            UserSeeder::class,
            AnnouncementSeeder::class,
            ComplaintSeeder::class,
            FreedomPostSeeder::class,
            DocumentRequestSeeder::class,
            AppointmentSeeder::class,
            QueueSeeder::class,
            JobSeeder::class,
            LostFoundSeeder::class,
        ]);
    }
}
