<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([BranchScope::class])]
class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'penalty_type',
        'penalty_amount',
        'penalty_grace_period',
        'booking_expiration_hours',
        'default_booking_fee'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
