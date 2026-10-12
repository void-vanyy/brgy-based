<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'barangay_id',
        'status',
        'phone',
        'purok',
        'address',
        'birth_date',
        'avatar',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Roles                                                               */
    /* ------------------------------------------------------------------ */

    /**
     * resident  — a person who files complaints, requests and bookings
     * staff     — barangay desk staff, sees only their own barangay
     * admin     — barangay official, sees only their own barangay
     * city_admin— city oversight, sees every barangay
     */
    public const ROLES = [
        'resident' => 'Resident',
        'staff' => 'Barangay Staff',
        'admin' => 'Barangay Admin',
        'city_admin' => 'City Admin',
    ];

    /** May access the /admin portal at all. */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'staff', 'city_admin'], true);
    }

    /** Sees every barangay rather than just their own. */
    public function isCityAdmin(): bool
    {
        return $this->role === 'city_admin';
    }

    /** Confined to a single barangay's records. */
    public function isBarangayScoped(): bool
    {
        return $this->isAdmin() && ! $this->isCityAdmin();
    }

    /* ------------------------------------------------------------------ */
    /* Barangay scoping                                                    */
    /* ------------------------------------------------------------------ */

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * Restricts a query to this user's barangay unless they are a city admin.
     *
     * Every admin listing funnels through this so no screen can accidentally
     * leak another barangay's records:
     *
     *     $query->visibleTo($request->user());
     */
    public function scopeVisibleTo($query, User $actor)
    {
        if ($actor->isCityAdmin() || $actor->barangay_id === null) {
            return $query;
        }

        return $query->where('barangay_id', $actor->barangay_id);
    }

    /* ------------------------------------------------------------------ */
    /* Relations                                                           */
    /* ------------------------------------------------------------------ */

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function queueTickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function freedomPosts(): HasMany
    {
        return $this->hasMany(FreedomPost::class);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));

        return strtoupper(substr($parts[0] ?? 'U', 0, 1).substr($parts[1] ?? '', 0, 1));
    }
}
