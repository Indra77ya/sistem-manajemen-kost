<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([BranchScope::class])]
class Branch extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'google_maps_url',
        'description',
        'image',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }
}
