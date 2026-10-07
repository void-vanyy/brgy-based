<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class QueueTicket extends Model
{
    protected $fillable = [
        'ticket_no',
        'user_id',
        'name_on_ticket',
        'service',
        'window',
        'status',
        'queue_date',
        'called_at',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'served_at' => 'datetime',
        ];
    }

    public const SERVICES = [
        'document_request' => 'Document Request',
        'complaint' => 'Complaint / Concern',
        'payment' => 'Payment & Fees',
        'consultation' => 'Legal Consultation',
        'general' => 'General Inquiry',
    ];

    public const STATUSES = [
        'waiting' => 'Waiting',
        'called' => 'Now Serving',
        'serving' => 'In Progress',
        'done' => 'Done',
        'skipped' => 'Skipped',
        'no_show' => 'No Show',
    ];

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceName(): string
    {
        return self::SERVICES[$this->service] ?? ucfirst($this->service);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** Ticket number formatted as A-001, A-002 ... per service prefix. */
    public static function nextNumber(string $date): string
    {
        $prefix = 'A';
        $last = static::whereDate('queue_date', $date)
            ->where('ticket_no', 'like', $prefix.'-%')
            ->orderByDesc('id')
            ->value('ticket_no');

        $seq = $last ? ((int) substr($last, 2)) + 1 : 1;

        return $prefix.'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    public function positionInLine(): int
    {
        return static::whereDate('queue_date', $this->queue_date)
            ->where('status', 'waiting')
            ->where('id', '<=', $this->id)
            ->count();
    }

    public function waitMinutes(): int
    {
        $ahead = static::whereDate('queue_date', $this->queue_date)
            ->where('status', 'waiting')
            ->where('id', '<', $this->id)
            ->count();

        return max(0, $ahead * 5);
    }

    public function scopeForDate($query, ?Carbon $date = null)
    {
        return $query->whereDate('queue_date', $date ?? Carbon::today());
    }
}
