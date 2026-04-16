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
        $invoice = Invoice::create([
            'branch_id' => $lease->branch_id,
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-' . $lease->id . '-' . now()->format('YmdHis'),
            'amount' => 0, // Will be updated after items created
            'due_date' => now()->addDays(3),
            'status' => 'unpaid',
        ]);

        // 1. Rent Item
        $invoice->items()->create([
            'description' => 'Sewa Kamar - ' . $lease->room->number,
            'amount' => $lease->room->price,
            'type' => 'rent',
        ]);

        // 2. Deposit Item (if any)
        if ($lease->deposit_amount > 0) {
            $invoice->items()->create([
                'description' => 'Uang Jaminan (Deposit)',
                'amount' => $lease->deposit_amount,
                'type' => 'deposit',
            ]);
        }

        // 3. Service Items (Wait, they might not be attached yet in standard created event if via Filament)
        // However, some implementations might call saved/updated.
        // For robustness, we will handle services in the first invoice generation.

        $invoice->updateTotal();
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

    /**
     * Handle the Lease "saved" event.
     * This might be a better place to sync services to the first invoice if they weren't available at 'created'
     */
    public function saved(Lease $lease): void
    {
        // Check if this is the first time services are being attached to a new lease
        $invoice = $lease->invoices()->oldest()->first();
        if ($invoice) {
            $existingServiceItems = $invoice->items()->where('type', 'service')->exists();
            if (!$existingServiceItems && $lease->services()->exists()) {
                foreach ($lease->services as $service) {
                    $invoice->items()->create([
                        'description' => 'Layanan: ' . $service->name,
                        'amount' => $service->price,
                        'type' => 'service',
                    ]);
                }
                $invoice->updateTotal();
            }
        }
    }
}
