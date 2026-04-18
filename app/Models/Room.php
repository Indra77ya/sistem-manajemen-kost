<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ScopedBy([BranchScope::class])]
class Room extends Model
{
    use LogsActivity;

    protected $fillable = ['branch_id', 'number', 'type', 'price', 'capacity', 'status', 'description', 'gallery'];

    protected $casts = [
        'gallery' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'room_service');
    }
}
