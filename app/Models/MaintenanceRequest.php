<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([BranchScope::class])]
class MaintenanceRequest extends Model
{
    protected $fillable = [
        'branch_id',
        'room_id',
        'maintenance_category_id',
        'user_id',
        'technician_id',
        'title',
        'description',
        'priority',
        'status',
        'started_at',
        'resolved_at',
        'attachment_before',
        'attachment_after',
        'total_cost',
        'is_charged_to_tenant',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'total_cost' => 'decimal:2',
            'is_charged_to_tenant' => 'boolean',
        ];
    }

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

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaintenanceCategory::class, 'maintenance_category_id');
    }
}
