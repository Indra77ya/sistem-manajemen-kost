<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = ['room_id', 'name', 'condition', 'quantity', 'description'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
