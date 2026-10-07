<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Eight appointments from yesterday out to ten days ahead, covering every
     * time slot and every status in Appointment::STATUSES.
     */
    public function run(): void
    {
        $residents = User::query()->where('role', 'resident')->orderBy('id')->get();

        if ($residents->isEmpty()) {
            return; // UserSeeder must run first.
        }

        $staff = User::query()->where('role', 'staff')->value('id')
            ?? User::query()->where('role', 'admin')->value('id');

        $slots = Appointment::SLOTS;

        $rows = [
            [
                'reference_no'    => 'APT-0001',
                'user_id'         => $residents[0]->id,
                'subject'         => 'Pick up certificate of residency',
                'office'          => 'Barangay Secretary',
                'purpose'         => 'Release of a signed certificate for a scholarship requirement.',
                'appointment_date' => now()->subDay()->toDateString(),
                'time_slot'       => $slots[0],
                'status'          => 'completed',
                'remarks'         => 'Released on time; resident signed the logbook.',
                'processed_by'    => $staff,
                'at'              => now()->subDays(3),
            ],
            [
                'reference_no'    => 'APT-0002',
                'user_id'         => $residents[1]->id,
                'subject'         => 'Senior citizen check-up coordination',
                'office'          => 'Barangay Health Center',
                'purpose'         => 'Arrange monthly maintenance medicine pickup for a senior parent.',
                'appointment_date' => now()->subDay()->toDateString(),
                'time_slot'       => $slots[3],
                'status'          => 'no_show',
                'remarks'         => 'No one arrived within 30 minutes of the slot. Resident may rebook without penalty.',
                'processed_by'    => $staff,
                'at'              => now()->subDays(2),
            ],
            [
                'reference_no'    => 'APT-0003',
                'user_id'         => $residents[2]->id,
                'subject'         => 'Consultation on a boundary dispute',
                'office'          => "Barangay Captain's Office",
                'purpose'         => 'Seek advice on a property boundary concern before filing a formal complaint.',
                'appointment_date' => now()->toDateString(),
                'time_slot'       => $slots[1],
                'status'          => 'cancelled',
                'remarks'         => 'Cancelled by the resident — parties settled the matter informally.',
                'processed_by'    => $staff,
                'at'              => now()->subDays(2),
            ],
            [
                'reference_no'    => 'APT-0004',
                'user_id'         => $residents[3]->id,
                'subject'         => 'Blotter follow-up and peace pact',
                'office'          => 'Peace and Order Desk',
                'purpose'         => 'Discuss the outcome of an earlier incident report with the desk investigator.',
                'appointment_date' => now()->addDay()->toDateString(),
                'time_slot'       => $slots[2],
                'status'          => 'confirmed',
                'remarks'         => 'Confirmed by SMS. Kagawad on duty will assist.',
                'processed_by'    => $staff,
                'at'              => now()->subDay(),
            ],
            [
                'reference_no'    => 'APT-0005',
                'user_id'         => $residents[4]->id,
                'subject'         => 'Business permit endorsement review',
                'office'          => 'Barangay Secretary',
                'purpose'         => 'Review of requirements for a home-based bakery endorsement.',
                'appointment_date' => now()->addDays(3)->toDateString(),
                'time_slot'       => $slots[4],
                'status'          => 'confirmed',
                'remarks'         => 'Applicant asked to bring a valid ID, barangay clearance and a photo of the workspace.',
                'processed_by'    => $staff,
                'at'              => now()->subHours(10),
            ],
            [
                'reference_no'    => 'APT-0006',
                'user_id'         => $residents[5]->id,
                'subject'         => 'Assistance referral for family food packs',
                'office'          => 'DSWD / Social Services Desk',
                'purpose'         => 'Request for a referral letter after the household lost its source of income.',
                'appointment_date' => now()->addDays(2)->toDateString(),
                'time_slot'       => $slots[5],
                'status'          => 'pending',
                'remarks'         => 'Awaiting confirmation from the social worker on duty.',
                'processed_by'    => null,
                'at'              => now()->subHours(6),
            ],
            [
                'reference_no'    => 'APT-0007',
                'user_id'         => $residents[0]->id,
                'subject'         => 'Youth leadership program briefing',
                'office'          => 'SK Office',
                'purpose'         => 'Orientation for volunteers joining the Sigla youth scholarship rollout.',
                'appointment_date' => now()->addDays(5)->toDateString(),
                'time_slot'       => $slots[0],
                'status'          => 'pending',
                'remarks'         => 'Submitted through the resident portal; awaiting a slot confirmation.',
                'processed_by'    => null,
                'at'              => now()->subHours(3),
            ],
            [
                'reference_no'    => 'APT-0008',
                'user_id'         => $residents[2]->id,
                'subject'         => 'Dental mission pre-registration',
                'office'          => 'Barangay Health Center',
                'purpose'         => 'Reserve a slot for the free dental extraction drive next week.',
                'appointment_date' => now()->addDays(10)->toDateString(),
                'time_slot'       => $slots[3],
                'status'          => 'pending',
                'remarks'         => 'Resident requested the earliest available morning slot.',
                'processed_by'    => null,
                'at'              => now()->subHour(),
            ],
        ];

        foreach ($rows as $row) {
            $at = $row['at'];
            unset($row['at']);

            $appointment = Appointment::query()->firstOrCreate(
                ['reference_no' => $row['reference_no']],
                $row
            );

            $appointment->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }
    }
}
