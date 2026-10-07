<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostFound extends Model
{
    protected $table = 'lost_found';

    protected $fillable = [
        'item_name',
        'category',
        'description',
        'status',
        'location',
        'date_occurred',
        'contact_info',
        'image',
        'reported_by',
    ];

    protected function casts(): array
    {
        return [
            'date_occurred' => 'date',
        ];
    }

    public const CATEGORIES = [
        'gadget' => 'Gadget / Electronics',
        'jewelry' => 'Jewelry',
        'documents' => 'IDs & Documents',
        'clothing' => 'Clothing',
        'accessory' => 'Bags & Accessories',
        'pet' => 'Pets & Animals',
        'others' => 'Others',
    ];

    public const STATUSES = [
        'lost' => 'Lost',
        'found' => 'Found',
        'claimed' => 'Claimed',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function categoryName(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
