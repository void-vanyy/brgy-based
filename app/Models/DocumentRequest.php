<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequest extends Model
{
    protected $fillable = [
        'reference_no',
        'user_id',
        'doc_type',
        'purpose',
        'copies',
        'fee',
        'status',
        'remarks',
        'processed_by',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'released_at' => 'datetime',
        ];
    }

    /** Master list of certificates, clearances and other documents a resident can request. */
    public const TYPES = [
        'barangay_clearance' => 'Barangay Clearance',
        'certificate_of_residency' => 'Certificate of Residency',
        'certificate_of_indigency' => 'Certificate of Indigency',
        'certificate_of_good_moral' => 'Certificate of Good Moral Character',
        'certificate_of_no_income' => 'Certificate of No Income',
        'certificate_of_live_birth' => 'Certificate of Live Birth (Cenomar)',
        'business_permit_endorsement' => 'Business Permit Endorsement',
        'barangay_id_application' => 'Barangay ID Application',
        'certificate_of_solo_parent' => 'Certificate of Solo Parent',
        'document_request' => 'Other Document Request',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'ready_for_release' => 'Ready for Release',
        'released' => 'Released',
        'rejected' => 'Rejected',
    ];

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function typeName(): string
    {
        return self::TYPES[$this->doc_type] ?? str_replace('_', ' ', ucfirst($this->doc_type));
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
