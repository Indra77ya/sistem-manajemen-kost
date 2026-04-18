<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([BranchScope::class])]
class Booking extends Model
{
    protected $fillable = [
        'branch_id',
        'room_id',
        'user_id',
        'check_in_date',
        'booking_fee',
        'status',
        'proof_of_payment',
        'expires_at'
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'expires_at' => 'datetime',
        'booking_fee' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
