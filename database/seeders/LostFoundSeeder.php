<?php

namespace Database\Seeders;

use App\Models\LostFound;
use App\Models\User;
use Illuminate\Database\Seeder;

class LostFoundSeeder extends Seeder
{
    /**
     * Eight lost & found postings — three lost, four found, one already claimed.
     */
    public function run(): void
    {
        $residents = User::query()->where('role', 'resident')->orderBy('id')->get();

        if ($residents->isEmpty()) {
            return; // UserSeeder must run first.
        }

        $rows = [
            [
                'item_name'     => 'Black iPhone 12 with a navy blue case',
                'category'      => 'gadget',
                'description'   => 'Left on the bench beside the basketball court after the evening league. The wallpaper shows a golden retriever and the phone is on silent.',
                'status'        => 'lost',
                'location'      => 'Barangay Covered Court',
                'date_occurred' => now()->subDays(2)->toDateString(),
                'contact_info'  => '0918 220 4471 (Maria Santos)',
                'reported_by'   => $residents[0]->id,
                'at'            => now()->subDays(2),
            ],
            [
                'item_name'     => 'Silver keychain with a tricycle charm',
                'category'      => 'others',
                'description'   => 'Set of house keys with a hand-made tricycle charm. Dropped somewhere between the health center and the market entrance.',
                'status'        => 'lost',
                'location'      => 'Path to the Public Market',
                'date_occurred' => now()->subDays(5)->toDateString(),
                'contact_info'  => '0918 220 4472 (Juan Reyes)',
                'reported_by'   => $residents[1]->id,
                'at'            => now()->subDays(5),
            ],
            [
                'item_name'     => 'Blue JanSport backpack with school supplies',
                'category'      => 'accessory',
                'description'   => 'Bag with notebooks, a geometry set and a brown envelope. Last seen hanging on the back of a chair in Zone 5 waiting shed.',
                'status'        => 'lost',
                'location'      => 'Zone 5 waiting shed',
                'date_occurred' => now()->subDays(8)->toDateString(),
                'contact_info'  => '0918 220 4475 (Cristina Villanueva)',
                'reported_by'   => $residents[4]->id,
                'at'            => now()->subDays(8),
            ],
            [
                'item_name'     => 'Brown leather wallet with two IDs',
                'category'      => 'accessory',
                'description'   => 'Found beside the drainage canal after the cleanup drive. Contains a driver’s licence, a voter ID and cash — claim at the barangay hall with a valid ID.',
                'status'        => 'found',
                'location'      => 'Mabini Street drainage canal',
                'date_occurred' => now()->subDays(3)->toDateString(),
                'contact_info'  => '(02) 8536-1188 — Barangay Hall',
                'reported_by'   => $residents[2]->id,
                'at'            => now()->subDays(3),
            ],
            [
                'item_name'     => 'Student ID — Maria Clara Academy',
                'category'      => 'documents',
                'description'   => 'Identification card of a grade 9 student, picked up near the elementary school gate. Returned to the barangay hall for safekeeping.',
                'status'        => 'found',
                'location'      => 'Sigla Elementary School gate',
                'date_occurred' => now()->subDays(4)->toDateString(),
                'contact_info'  => '(02) 8536-1188 — Barangay Hall',
                'reported_by'   => $residents[3]->id,
                'at'            => now()->subDays(4),
            ],
            [
                'item_name'     => 'Gold-plated pendant with a photo locket',
                'category'      => 'jewelry',
                'description'   => 'Found on the bench near the plaza fountain. The owner can describe the photo inside before it is released.',
                'status'        => 'found',
                'location'      => 'Public Plaza fountain area',
                'date_occurred' => now()->subDays(6)->toDateString(),
                'contact_info'  => '0918 220 4473 (Rosalie Mendoza)',
                'reported_by'   => $residents[2]->id,
                'at'            => now()->subDays(6),
            ],
            [
                'item_name'     => 'White Shih Tzu named Bambi',
                'category'      => 'pet',
                'description'   => 'Small white dog with a red collar, found wandering along Narra Avenue. Fed and kept at the barangay hall pending the owner.',
                'status'        => 'found',
                'location'      => 'Narra Avenue, Zone 3',
                'date_occurred' => now()->subDay()->toDateString(),
                'contact_info'  => '0995 214 7781 — Barangay Tanod Desk',
                'reported_by'   => $residents[5]->id,
                'at'            => now()->subDay(),
            ],
            [
                'item_name'     => 'Navy blue umbrella with a wooden handle',
                'category'      => 'others',
                'description'   => 'Umbrella left inside the clinic after the medical mission. Claimed by the owner at the health center with a description of the handle engraving.',
                'status'        => 'claimed',
                'location'      => 'Barangay Health Center',
                'date_occurred' => now()->subDays(9)->toDateString(),
                'contact_info'  => '(02) 8536-1188 — Barangay Hall',
                'reported_by'   => $residents[4]->id,
                'at'            => now()->subDays(9),
            ],
        ];

        foreach ($rows as $row) {
            $at = $row['at'];
            unset($row['at']);

            $entry = LostFound::query()->firstOrCreate(['item_name' => $row['item_name']], $row);

            $entry->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }
    }
}
