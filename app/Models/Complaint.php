<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    protected $fillable = [
        'reference_no',
        'user_id',
        'title',
        'description',
        'category',
        'location',
        'purok',
        'latitude',
        'longitude',
        'priority',
        'status',
        'assigned_to',
        'admin_remarks',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'resolved_at' => 'datetime',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ComplaintUpdate::class);
    }

    public function scopeMine($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
