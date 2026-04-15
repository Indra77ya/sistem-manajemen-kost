<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Lease;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'kost:generate-invoices';
    protected $description = 'Generate monthly invoices for active leases including recurring services';

    public function handle()
    {
        $today = Carbon::today();
        $dayOfMonth = $today->day;

        $activeLeases = Lease::where('status', 'active')
            ->where('billing_date', $dayOfMonth)
            ->get();

        foreach ($activeLeases as $lease) {
            // Check if invoice already exists for this month/year
            $exists = Invoice::where('lease_id', $lease->id)
                ->whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->exists();

            if (!$exists) {
                $invoice = Invoice::create([
                    'branch_id' => $lease->branch_id,
                    'lease_id' => $lease->id,
                    'invoice_number' => 'INV-' . $lease->id . '-' . $today->format('Ymd'),
                    'amount' => 0,
                    'due_date' => $today->copy()->addDays(7),
                    'status' => 'unpaid',
                ]);

                // 1. Rent Item
                $invoice->items()->create([
                    'description' => 'Sewa Kamar - ' . $lease->room->number . ' (' . $today->format('F Y') . ')',
                    'amount' => $lease->room->price,
                    'type' => 'rent',
                ]);

                // 2. Recurring Service Items
                foreach ($lease->services as $service) {
                    if ($service->is_recurring) {
                        $invoice->items()->create([
                            'description' => 'Layanan: ' . $service->name,
                            'amount' => $service->price,
                            'type' => 'service',
                        ]);
                    }
                }

                $invoice->updateTotal();
                $this->info("Generated invoice for Lease #{$lease->id}");
            }
        }

        return 0;
    }
}
