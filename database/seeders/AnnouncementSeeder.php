<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Six notices covering every category — one pinned, one still a draft,
     * so both the public list and the admin filters have something to show.
     *
     * Three are city-wide (null barangay) and three belong to a single
     * barangay, which is what makes the scoping visible when you switch
     * between a city admin, a barangay admin and a resident.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->value('id');

        $ids = Barangay::query()->pluck('id', 'name');

        // A null barangay_id means the notice is published city-wide. Half the
        // set is city-wide and half belongs to a single barangay, so signing in
        // as a barangay admin shows their own notices plus the city ones, while
        // the city admin sees all six.
        $notices = [
            [
                'title'      => 'Typhoon Preparedness Advisory: Signal No. 2 over Laguna',
                'body'       => "The Low Pressure Area east of Luzon has intensified. Residents of all puroks are advised to secure loose roofing, charge emergency lights and monitor official advisories.\n\nEvacuation points are the covered courts and school gymnasiums of each barangay. The City Disaster Risk Reduction hotline stays open 24/7 during the storm.",
                'category'   => 'emergency',
                'is_pinned'  => true,
                'is_published' => true,
                'barangay'   => null, // city-wide
                'event_date' => now()->addDays(2)->toDateString(),
                'location'   => 'Santa Rosa City — all barangays',
                'published_at' => now()->subDay(),
            ],
            [
                'title'      => 'Tagapo Quarterly Clean-up Drive',
                'body'       => "Join the quarterly clean-up of our esteros, drainage canals and roadside verges. Bring gloves, sacks and your own water tumbler.\n\nAssembly is at 6:30 AM at the Tagapo Covered Court. Purok captains will distribute cleaning kits per team.",
                'category'   => 'program',
                'is_pinned'  => false,
                'is_published' => true,
                'barangay'   => 'Tagapo',
                'event_date' => now()->addDays(6)->toDateString(),
                'location'   => 'Tagapo Covered Court',
                'published_at' => now()->subDays(2),
            ],
            [
                'title'      => 'Free Medical & Dental Mission',
                'body'       => "In partnership with the City Health Office: free consultation, tooth extraction, blood pressure screening, and blood sugar testing for senior citizens and indigent residents.\n\nFirst come, first served. Bring a valid ID and your barangay residency proof, if available.",
                'category'   => 'health',
                'is_pinned'  => false,
                'is_published' => true,
                'barangay'   => null, // city-wide
                'event_date' => now()->addDays(9)->toDateString(),
                'location'   => 'Santa Rosa City Health Office',
                'published_at' => now()->subDays(3),
            ],
            [
                'title'      => 'Labas Inter-Purok Basketball League Finals',
                'body'       => "Purok 3 vs. Purok 6 for the barangay cup championship! Tip-off at 4:00 PM, followed by the awarding ceremony and a short program for our youth athletes.\n\nFree entrance for all residents. Food stalls open at 2:00 PM.",
                'category'   => 'event',
                'is_pinned'  => false,
                'is_published' => true,
                'barangay'   => 'Labas',
                'event_date' => now()->addDays(12)->toDateString(),
                'location'   => 'Labas Covered Court',
                'published_at' => now()->subDays(4),
            ],
            [
                'title'      => 'Scholarship Application Window for SY 2026–2027',
                'body'       => "The Sangguniang Kabataan Federation is now accepting applications for the Santa Rosa Youth Scholarship: tuition stipend and school supplies for elementary, junior and senior high school students.\n\nRequirements: certificate of enrollment, certificate of residency, and the latest report card. Priority goes to indigent and top-performing students.",
                'category'   => 'information',
                'is_pinned'  => false,
                'is_published' => true,
                'barangay'   => null, // city-wide
                'event_date' => now()->addDays(15)->toDateString(),
                'location'   => 'City Hall records counter',
                'published_at' => now()->subDays(5),
            ],
            [
                'title'      => 'Ordinance No. 2026-03: Regulation of Unleashed Dogs',
                'body'       => "An ordinance requiring residents to keep dogs leashed or confined within their premises, mandating registration for pets four months and older, and prescribing penalties for strays found roaming the streets.\n\nDraft awaiting publication after the Sangguniang Barangay session. Feedback may be submitted through the freedom wall.",
                'category'   => 'ordinance',
                'is_pinned'  => false,
                'is_published' => false,
                'barangay'   => 'Kanluran',
                'event_date' => now()->addDays(20)->toDateString(),
                'location'   => 'Kanluran Barangay Session Hall',
                'published_at' => null,
            ],
        ];

        foreach ($notices as $notice) {
            $barangay = $notice['barangay'];
            unset($notice['barangay']);

            if ($barangay !== null && ! isset($ids[$barangay])) {
                $this->command?->error("Unknown barangay: {$barangay}");

                continue;
            }

            Announcement::query()->firstOrCreate(
                ['title' => $notice['title']],
                $notice + [
                    'created_by' => $admin,
                    'barangay_id' => $barangay !== null ? $ids[$barangay] : null,
                ]
            );
        }
    }
}
