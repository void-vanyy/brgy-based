<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'staff'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

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
