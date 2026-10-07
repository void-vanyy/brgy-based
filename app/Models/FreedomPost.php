<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreedomPost extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'topic',
        'is_anonymous',
        'reactions',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'reactions' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Anonymous posts never expose the author outside of the author's own session.
     */
    public function displayAuthor(): string
    {
        return $this->is_anonymous ? 'Anonymous Resident' : ($this->author?->name ?? 'Resident');
    }
}
