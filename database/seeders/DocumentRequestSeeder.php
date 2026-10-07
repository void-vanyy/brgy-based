<?php

namespace Database\Seeders;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentRequestSeeder extends Seeder
{
    /**
     * Eight certificate requests spanning every status in the workflow,
     * with realistic fees, remarks and processing staff.
     */
    public function run(): void
    {
        $residents = User::query()->where('role', 'resident')->orderBy('id')->get();

        if ($residents->isEmpty()) {
            return; // UserSeeder must run first.
        }

        $staff = User::query()->where('role', 'staff')->value('id')
            ?? User::query()->where('role', 'admin')->value('id');

        $rows = [
            [
                'reference_no' => 'DOC-0001',
                'user_id'      => $residents[0]->id,
                'doc_type'     => 'barangay_clearance',
                'purpose'      => 'Local employment requirement for a customer service post in the city.',
                'copies'       => 2,
                'fee'          => 50,
                'status'       => 'released',
                'remarks'      => 'Claimed at Window 2. ID photocopy attached to the transaction record.',
                'processed_by' => $staff,
                'released_at'  => now()->subDays(9),
                'at'           => now()->subDays(12),
            ],
            [
                'reference_no' => 'DOC-0002',
                'user_id'      => $residents[1]->id,
                'doc_type'     => 'certificate_of_residency',
                'purpose'      => 'Proof of residence for a scholarship application at the city hall.',
                'copies'       => 1,
                'fee'          => 30,
                'status'       => 'ready_for_release',
                'remarks'      => 'Signed by the Punong Barangay. Ready for pickup — resident advised by SMS.',
                'processed_by' => $staff,
                'released_at'  => null,
                'at'           => now()->subDays(4),
            ],
            [
                'reference_no' => 'DOC-0003',
                'user_id'      => $residents[2]->id,
                'doc_type'     => 'certificate_of_indigency',
                'purpose'      => 'Supporting document for medical assistance at the city hospital social service.',
                'copies'       => 1,
                'fee'          => 30,
                'status'       => 'approved',
                'remarks'      => 'Approved pending release; purok captain validation completed.',
                'processed_by' => $staff,
                'released_at'  => null,
                'at'           => now()->subDays(3),
            ],
            [
                'reference_no' => 'DOC-0004',
                'user_id'      => $residents[3]->id,
                'doc_type'     => 'business_permit_endorsement',
                'purpose'      => 'Endorsement for a home-based sari-sari store permit renewal.',
                'copies'       => 2,
                'fee'          => 50,
                'status'       => 'under_review',
                'remarks'      => 'Checking the barangay clearance record and the applicant’s tax reference.',
                'processed_by' => $staff,
                'released_at'  => null,
                'at'           => now()->subDays(2),
            ],
            [
                'reference_no' => 'DOC-0005',
                'user_id'      => $residents[4]->id,
                'doc_type'     => 'barangay_id_application',
                'purpose'      => 'New barangay ID after transferring from another district.',
                'copies'       => 1,
                'fee'          => 30,
                'status'       => 'pending',
                'remarks'      => 'Awaiting 1x1 photo and proof of residency from the applicant.',
                'processed_by' => null,
                'released_at'  => null,
                'at'           => now()->subDay(),
            ],
            [
                'reference_no' => 'DOC-0006',
                'user_id'      => $residents[5]->id,
                'doc_type'     => 'certificate_of_good_moral',
                'purpose'      => 'Requirement for a school transfer credential.',
                'copies'       => 1,
                'fee'          => 30,
                'status'       => 'rejected',
                'remarks'      => 'Rejected: an open complaint record must be resolved first. Resident may reapply once the case is closed.',
                'processed_by' => $staff,
                'released_at'  => null,
                'at'           => now()->subDays(6),
            ],
            [
                'reference_no' => 'DOC-0007',
                'user_id'      => $residents[0]->id,
                'doc_type'     => 'certificate_of_solo_parent',
                'purpose'      => 'Requirement for the city solo parent welfare benefit.',
                'copies'       => 3,
                'fee'          => 30,
                'status'       => 'released',
                'remarks'      => 'Released with the DSWD referral letter attached.',
                'processed_by' => $staff,
                'released_at'  => now()->subDays(2),
                'at'           => now()->subDays(7),
            ],
            [
                'reference_no' => 'DOC-0008',
                'user_id'      => $residents[2]->id,
                'doc_type'     => 'certificate_of_no_income',
                'purpose'      => 'Supporting document for educational financial assistance.',
                'copies'       => 1,
                'fee'          => 30,
                'status'       => 'pending',
                'remarks'      => 'Queued for assessment at the records counter.',
                'processed_by' => null,
                'released_at'  => null,
                'at'           => now()->subHours(9),
            ],
        ];

        foreach ($rows as $row) {
            $at = $row['at'];
            unset($row['at']);

            $document = DocumentRequest::query()->firstOrCreate(
                ['reference_no' => $row['reference_no']],
                $row
            );

            $document->forceFill([
                'created_at' => $at,
                'updated_at' => $document->released_at ?? $at,
            ])->save();
        }
    }
}
