<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ScopedBy([BranchScope::class])]
class Announcement extends Model
{
    use LogsActivity;

    protected $fillable = [
        'branch_id',
        'title',
        'content',
        'type',
        'published_at',
        'expired_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
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
}
