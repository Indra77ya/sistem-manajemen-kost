<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([BranchScope::class])]
class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'branch_id',
        'amount',
        'payment_date',
        'payment_method',
        'proof_of_payment',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
