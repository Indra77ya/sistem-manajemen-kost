<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([BranchScope::class])]
class Room extends Model
{
    protected $fillable = [
        'branch_id',
        'room_number',
        'type',
        'price',
        'capacity',
        'billing_type',
        'description',
        'is_available',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'room_facility');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }
}
