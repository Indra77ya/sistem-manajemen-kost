<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = ['name', 'brand', 'category', 'description'];

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
