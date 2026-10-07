<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    /** Livelihood listings — deliberately not the framework's queue `jobs` table. */
    protected $table = 'job_postings';

    protected $fillable = [
        'title',
        'company',
        'category',
        'location',
        'employment_type',
        'salary',
        'description',
        'requirements',
        'contact_person',
        'contact_number',
        'contact_email',
        'deadline',
        'status',
        'is_featured',
        'posted_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    public const CATEGORIES = [
        'general' => 'General / Miscellaneous',
        'construction' => 'Construction & Trades',
        'food_service' => 'Food & Hospitality',
        'retail' => 'Retail & Sales',
        'healthcare' => 'Health & Caregiving',
        'agriculture' => 'Agriculture & Livelihood',
        'bpo' => 'BPO & Office',
        'domestic' => 'Domestic & Errand',
        'technical' => 'Technical & Skilled',
    ];

    public const TYPES = [
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
        'contract' => 'Contract',
        'temporary' => 'Temporary / Project',
        'freelance' => 'Freelance',
    ];

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function categoryName(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function typeName(): string
    {
        return self::TYPES[$this->employment_type] ?? ucfirst($this->employment_type);
    }
}
