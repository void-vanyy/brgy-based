<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ComplaintUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    /**
     * Ten believable complaints across every status and priority.
     *
     * Seven carry coordinates (so the map has markers) and three do not.
     * Each one gets a short, coherent update trail from "received" up to its
     * current status, written by the barangay admin or staff.
     */
    public function run(): void
    {
        $residents = User::query()->where('role', 'resident')->orderBy('id')->get();

        if ($residents->isEmpty()) {
            return; // UserSeeder must run first.
        }

        $admin = User::query()->where('role', 'admin')->value('id');
        $staff = User::query()->where('role', 'staff')->value('id');

        foreach ($this->rows($residents, $admin, $staff) as $row) {
            $story = $row['story'];
            unset($row['story']);

            $complaint = Complaint::query()->firstOrCreate(
                ['reference_no' => $row['reference_no']],
                $row
            );

            // created_at / updated_at / resolved_at are not mass assignable.
            $complaint->forceFill([
                'created_at'  => $story[0]['at'],
                'updated_at'  => $story[count($story) - 1]['at'],
                'resolved_at' => $complaint->resolved_at ?? (
                    in_array($row['status'], ['resolved', 'closed'], true)
                        ? $story[count($story) - 1]['at']
                        : null
                ),
            ])->save();

            if ($complaint->updates()->exists()) {
                continue;
            }

            foreach ($story as $update) {
                $record = ComplaintUpdate::query()->create([
                    'complaint_id' => $complaint->id,
                    'status'       => $update['status'],
                    'title'        => $update['title'],
                    'note'         => $update['note'],
                    'updated_by'   => $update['by'] === 'admin' ? $admin : $staff,
                ]);

                $record->forceFill(['created_at' => $update['at'], 'updated_at' => $update['at']])->save();
            }
        }
    }

    private function rows($residents, ?int $admin, ?int $staff): array
    {
        $resident = fn (int $index): int => $residents[$index % $residents->count()]->id;

        return [
            [
                'reference_no'  => 'CMP-0001',
                'user_id'       => $resident(0),
                'title'         => 'Clogged drainage along Mabini Street',
                'description'   => 'The concrete canal in front of number 21 is completely blocked with silt and household waste. Water rises to knee level whenever it rains and now smells really bad.',
                'category'      => 'sanitation',
                'location'      => 'Mabini Street, near the estero bridge',
                'purok'         => 'Purok 2',
                'latitude'      => 14.6058,
                'longitude'     => 121.0421,
                'priority'      => 'high',
                'status'        => 'resolved',
                'assigned_to'   => $staff,
                'admin_remarks' => 'Cleared by the barangay maintenance crew. Desilting will be repeated before the peak of the rainy season.',
                'story'         => [
                    ['status' => 'received',   'title' => 'Complaint logged',           'note' => 'Report filed through the resident portal with the exact location and two photos.', 'by' => 'admin', 'at' => now()->subDays(6)],
                    ['status' => 'in_progress', 'title' => 'Sanitation team dispatched', 'note' => 'Assigned to the barangay maintenance crew. Pumping and desilting scheduled for the next dry window.', 'by' => 'staff', 'at' => now()->subDays(5)],
                    ['status' => 'resolved',   'title' => 'Drainage cleared',           'note' => 'Silt and household waste removed. The canal is draining normally and the area has been disinfected.', 'by' => 'staff', 'at' => now()->subDays(4)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0002',
                'user_id'       => $resident(1),
                'title'         => 'Broken streetlight at the corner of Rizal Avenue',
                'description'   => 'The LED streetlight at the corner has been out for two weeks. The crossing near the eatery is very dark at night and pedestrians cannot see trikes coming.',
                'category'      => 'infrastructure',
                'location'      => 'Rizal Avenue corner Sampaguita Street',
                'purok'         => 'Purok 1',
                'latitude'      => 14.6034,
                'longitude'     => 121.0396,
                'priority'      => 'medium',
                'status'        => 'in_progress',
                'assigned_to'   => $staff,
                'admin_remarks' => 'Endorsed to the city electrical unit through our barangay liaison.',
                'story'         => [
                    ['status' => 'received',    'title' => 'Complaint logged',        'note' => 'Resident reported the busted streetlight with the pole number for identification.', 'by' => 'admin', 'at' => now()->subDays(8)],
                    ['status' => 'in_progress', 'title' => 'Endorsed to the utility desk', 'note' => 'Referred to the city electrical unit. Replacement of the LED head is requested; awaiting the works schedule.', 'by' => 'staff', 'at' => now()->subDays(6)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0003',
                'user_id'       => $resident(2),
                'title'         => 'Vehicles blocking the fire lane',
                'description'   => 'Three vehicles are parked across the fire lane every night. A fire truck or ambulance would never fit through, especially during the evening rush.',
                'category'      => 'traffic',
                'location'      => 'Fire lane behind the covered court',
                'purok'         => 'Purok 3',
                'latitude'      => 14.6049,
                'longitude'     => 121.0433,
                'priority'      => 'urgent',
                'status'        => 'received',
                'assigned_to'   => null,
                'admin_remarks' => null,
                'story'         => [
                    ['status' => 'received', 'title' => 'Complaint logged',  'note' => 'Filed at 9:42 PM with plate numbers and a photo of the blocked lane.', 'by' => 'admin', 'at' => now()->subDays(1)],
                    ['status' => 'received', 'title' => 'Queued for validation', 'note' => 'Traffic desk will conduct a night validation round before issuing warning notices to the owners.', 'by' => 'staff', 'at' => now()->subHours(14)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0004',
                'user_id'       => $resident(3),
                'title'         => 'Loud videoke sessions past midnight',
                'description'   => 'The videoke machine next door runs until 2:00 AM on weekdays. Children in our house cannot sleep and I have work the next morning.',
                'category'      => 'noise',
                'location'      => 'Kalachuchi Street, Zone 4',
                'purok'         => 'Purok 4',
                'latitude'      => null,
                'longitude'     => null,
                'priority'      => 'low',
                'status'        => 'closed',
                'assigned_to'   => $admin,
                'admin_remarks' => 'Closed after two clean follow-up checks. The household signed a written commitment to observe the quiet-hours ordinance.',
                'story'         => [
                    ['status' => 'received',    'title' => 'Complaint logged',      'note' => 'Concern submitted anonymously by a neighbour through the resident portal.', 'by' => 'admin', 'at' => now()->subDays(12)],
                    ['status' => 'in_progress', 'title' => 'Peace and order mediation', 'note' => 'Kagawad visited the household and explained Ordinance No. 2019-04 on quiet hours (10:00 PM to 6:00 AM).', 'by' => 'admin', 'at' => now()->subDays(11)],
                    ['status' => 'closed',      'title' => 'Closed — no recurrence', 'note' => 'Two follow-up checks showed no further incidents. Case closed.', 'by' => 'staff', 'at' => now()->subDays(7)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0005',
                'user_id'       => $resident(4),
                'title'         => 'Stray dogs roaming near the school zone',
                'description'   => 'A pack of five stray dogs chases students along the route to the elementary school every morning. Two children have already fallen off their bikes.',
                'category'      => 'peace_and_order',
                'location'      => 'School zone, Narra Avenue',
                'purok'         => 'Purok 3',
                'latitude'      => 14.6025,
                'longitude'     => 121.0418,
                'priority'      => 'high',
                'status'        => 'in_progress',
                'assigned_to'   => $admin,
                'admin_remarks' => 'Awaiting the city veterinary office catch-and-neuter schedule.',
                'story'         => [
                    ['status' => 'received',    'title' => 'Complaint logged',    'note' => 'Reported by three parents from the same purok; cases consolidated under one reference.', 'by' => 'admin', 'at' => now()->subDays(4)],
                    ['status' => 'in_progress', 'title' => 'City veterinary office coordinated', 'note' => 'Request for a catch-and-neuter team submitted. In the meantime, tanods patrol the school route at 6:30 AM and pet owners are reminded to leash their animals.', 'by' => 'admin', 'at' => now()->subDays(3)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0006',
                'user_id'       => $resident(5),
                'title'         => 'Stagnant water and dengue risk in the vacant lot',
                'description'   => 'The abandoned lot is full of old tyres and cans holding rainwater. Mosquitoes are everywhere in the afternoon and two houses on our street already had fever.',
                'category'      => 'health',
                'location'      => 'Vacant lot, Molave Street',
                'purok'         => 'Purok 6',
                'latitude'      => 14.6063,
                'longitude'     => 121.0402,
                'priority'      => 'urgent',
                'status'        => 'resolved',
                'assigned_to'   => $staff,
                'admin_remarks' => 'Site cleared and fogged. Owner advised to keep the lot drained; re-inspection next month.',
                'story'         => [
                    ['status' => 'received',    'title' => 'Complaint logged',   'note' => 'Health concern logged after the purok health worker confirmed mosquito breeding sites.', 'by' => 'admin', 'at' => now()->subDays(9)],
                    ['status' => 'in_progress', 'title' => 'Clean-up and fogging scheduled', 'note' => 'Coordinate drive with the City Health Office set; owners of the lot notified to remove debris.', 'by' => 'staff', 'at' => now()->subDays(8)],
                    ['status' => 'resolved',    'title' => 'Site treated and cleared', 'note' => 'Discarded tyres and containers hauled away, premises fogged, and larvicides applied to remaining puddles.', 'by' => 'staff', 'at' => now()->subDays(7)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0007',
                'user_id'       => $resident(0),
                'title'         => 'Uneven road shoulder causing falls',
                'description'   => 'The shoulder of the road dropped about half a foot after the pipe repair. Bikes and tricycles trip on it, and a senior resident already fell last week.',
                'category'      => 'infrastructure',
                'location'      => 'Zone 7 approach, near the water tank',
                'purok'         => 'Purok 7',
                'latitude'      => null,
                'longitude'     => null,
                'priority'      => 'medium',
                'status'        => 'on_hold',
                'assigned_to'   => $staff,
                'admin_remarks' => 'The road section belongs to the city engineering district. Endorsed and pending assessment; temporary barriers installed.',
                'story'         => [
                    ['status' => 'received', 'title' => 'Complaint logged', 'note' => 'Reported with photos showing the drop-off and the warning cone the purok put up.', 'by' => 'admin', 'at' => now()->subDays(10)],
                    ['status' => 'on_hold',  'title' => 'Held pending city assessment', 'note' => 'The section is a city road, so the request was endorsed to city engineering. Warning barriers were placed while we wait.', 'by' => 'staff', 'at' => now()->subDays(9)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0008',
                'user_id'       => $resident(1),
                'title'         => 'No water pressure between 6 and 8 AM',
                'description'   => 'For the past week there is no pressure in our line during the morning peak. We have to fetch water from the barangay station before going to work.',
                'category'      => 'other',
                'location'      => 'Bonifacio Street, Zone 2',
                'purok'         => 'Purok 2',
                'latitude'      => null,
                'longitude'     => null,
                'priority'      => 'medium',
                'status'        => 'received',
                'assigned_to'   => null,
                'admin_remarks' => null,
                'story'         => [
                    ['status' => 'received', 'title' => 'Complaint logged', 'note' => 'Logged on behalf of six households who share the same service line.', 'by' => 'admin', 'at' => now()->subHours(20)],
                    ['status' => 'received', 'title' => 'Forwarded to the water district', 'note' => 'Complaint sent to the district consumer desk; awaiting their reference number so residents can follow up directly.', 'by' => 'staff', 'at' => now()->subHours(5)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0009',
                'user_id'       => $resident(2),
                'title'         => 'Unpermitted garbage burning at the vacant lot',
                'description'   => 'Someone burns household trash at the vacant lot almost every night. The smoke enters our windows and it is dangerous for my asthmatic father.',
                'category'      => 'sanitation',
                'location'      => 'Corner of Sampaguita and Ilang-Ilang Streets',
                'purok'         => 'Purok 1',
                'latitude'      => 14.6037,
                'longitude'     => 121.0437,
                'priority'      => 'high',
                'status'        => 'resolved',
                'assigned_to'   => $staff,
                'admin_remarks' => 'Warning issued under the clean air ordinance. Household was given a segregation bin and enrolled in collection pickup.',
                'story'         => [
                    ['status' => 'received',    'title' => 'Complaint logged',     'note' => 'Report received with the approximate time of the burning (around 7:30 PM).', 'by' => 'admin', 'at' => now()->subDays(14)],
                    ['status' => 'in_progress', 'title' => 'Warning issued',       'note' => 'Tanods identified the household and issued a written warning explaining the anti-burning ordinance.', 'by' => 'staff', 'at' => now()->subDays(13)],
                    ['status' => 'resolved',    'title' => 'Burning stopped',      'note' => 'No recurrence in the past ten days. The household now segregates waste and joins the scheduled collection.', 'by' => 'staff', 'at' => now()->subDays(6)],
                ],
            ],
            [
                'reference_no'  => 'CMP-0010',
                'user_id'       => $resident(3),
                'title'         => 'Altercation near the covered court',
                'description'   => 'Two groups had a shouting match after the basketball game and bottles were thrown near the bleachers where children were standing.',
                'category'      => 'peace_and_order',
                'location'      => 'Covered court bleachers',
                'purok'         => 'Purok 3',
                'latitude'      => 14.6046,
                'longitude'     => 121.0389,
                'priority'      => 'urgent',
                'status'        => 'closed',
                'assigned_to'   => $admin,
                'admin_remarks' => 'Both parties signed an amicable settlement. Curfew reminders were posted at the court entrance.',
                'story'         => [
                    ['status' => 'received',    'title' => 'Complaint logged',   'note' => 'Reported by the game coordinator at 8:15 PM while tanods were being dispatched.', 'by' => 'admin', 'at' => now()->subDays(16)],
                    ['status' => 'in_progress', 'title' => 'Tanods responded',   'note' => 'Team secured the area, brought the parties to the barangay hall and recorded statements.', 'by' => 'admin', 'at' => now()->subDays(16)],
                    ['status' => 'resolved',    'title' => 'Amicable settlement', 'note' => 'Both groups agreed in writing to settle the matter and to observe curfew during league games.', 'by' => 'admin', 'at' => now()->subDays(15)],
                    ['status' => 'closed',      'title' => 'Case closed',        'note' => 'No further incidents reported. Case endorsed to the peace and order committee for monitoring.', 'by' => 'staff', 'at' => now()->subDays(11)],
                ],
            ],
        ];
    }
}
