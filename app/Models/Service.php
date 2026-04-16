<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([BranchScope::class])]
class Service extends Model
{
    protected $fillable = ['branch_id', 'name', 'price', 'is_recurring'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function leases(): BelongsToMany
    {
        return $this->belongsToMany(Lease::class);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }
}
