<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'category',
        'is_pinned',
        'is_published',
        'event_date',
        'location',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderByDesc('is_pinned')->latest();
    }
}
