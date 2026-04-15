<?php

namespace App\Observers;

use App\Models\Lease;
use App\Models\Invoice;
use Carbon\Carbon;

class LeaseObserver
{
    /**
     * Handle the Lease "created" event.
     */
    public function created(Lease $lease): void
    {
        $lease->room->update(['status' => 'occupied']);

        // Generate first invoice automatically
        Invoice::create([
            'branch_id' => $lease->branch_id,
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-' . $lease->id . '-' . now()->format('YmdHis'),
            'amount' => $lease->room->price,
            'due_date' => now()->addDays(3),
            'status' => 'unpaid',
        ]);
    }

    /**
     * Handle the Lease "updated" event.
     */
    public function updated(Lease $lease): void
    {
        if ($lease->isDirty('status')) {
            if ($lease->status === 'active') {
                $lease->room->update(['status' => 'occupied']);
            } elseif ($lease->status === 'completed' || $lease->status === 'cancelled') {
                $lease->room->update(['status' => 'available']);
            }
        }
    }
}
