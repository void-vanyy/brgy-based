<?php

namespace App\Models;

use App\Models\Concerns\ScopedToBarangay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreedomPost extends Model
{
    use ScopedToBarangay;

    protected $fillable = [
        'user_id',
        'barangay_id',
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

    /**
     * The account that wrote the post. The foreign key has to be named:
     * `belongsTo(User::class)` on its own would infer `author_id` from this
     * method, and the column is `user_id` — the author is only ever surfaced
     * through displayAuthor(), which never shows it for anonymous posts.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Anonymous posts never expose the author outside of the author's own session.
     */
    public function displayAuthor(): string
    {
        return $this->is_anonymous ? 'Anonymous Resident' : ($this->author?->name ?? 'Resident');
    }
}
