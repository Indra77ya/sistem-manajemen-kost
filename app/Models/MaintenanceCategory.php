<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_priority',
        'default_technician_id',
    ];

    public function defaultTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_technician_id');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
