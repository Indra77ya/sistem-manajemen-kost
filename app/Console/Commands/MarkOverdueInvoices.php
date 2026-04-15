<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'kost:mark-overdue';
    protected $description = 'Mark unpaid invoices as overdue and apply penalties';

    public function handle()
    {
        $today = Carbon::today();

        // 1. Mark as Overdue
        Invoice::where('status', 'unpaid')
            ->where('due_date', '<', $today)
            ->update(['status' => 'overdue']);

        // 2. Apply Penalties
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->with(['branch', 'items'])
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $branch = $invoice->branch;
            if ($branch->penalty_type === 'none' || $branch->penalty_amount <= 0) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_date);
            $daysOverdue = $today->diffInDays($dueDate);
            $gracePeriod = $branch->penalty_grace_period;

            if ($daysOverdue <= $gracePeriod) {
                continue;
            }

            $penaltyToApply = 0;
            $description = "Denda Keterlambatan";

            if ($branch->penalty_type === 'flat') {
                // Check if flat penalty already applied
                $alreadyApplied = $invoice->items()->where('type', 'penalty')->exists();
                if (!$alreadyApplied) {
                    $penaltyToApply = $branch->penalty_amount;
                }
            } elseif ($branch->penalty_type === 'daily') {
                // Calculate how many days to charge for
                // Days to charge = Total days overdue - grace period
                $chargeableDays = $daysOverdue - $gracePeriod;

                // Find existing daily penalty item to update or create
                $penaltyItem = $invoice->items()->where('type', 'penalty')->first();
                $currentPenaltyAmount = $penaltyItem ? $penaltyItem->amount : 0;
                $newTotalPenalty = $branch->penalty_amount * $chargeableDays;

                $penaltyToApply = $newTotalPenalty - $currentPenaltyAmount;

                if ($penaltyToApply > 0) {
                    if ($penaltyItem) {
                        $penaltyItem->update([
                            'amount' => $newTotalPenalty,
                            'description' => "Denda Keterlambatan ($chargeableDays hari)"
                        ]);
                        $penaltyToApply = 0; // Already updated existing, don't create new
                    } else {
                        $penaltyToApply = $newTotalPenalty;
                        $description = "Denda Keterlambatan ($chargeableDays hari)";
                    }
                } else {
                    $penaltyToApply = 0;
                }
            }

            if ($penaltyToApply > 0) {
                $invoice->items()->create([
                    'description' => $description,
                    'amount' => $penaltyToApply,
                    'type' => 'penalty',
                ]);
            }

            $invoice->updateTotal();
        }

        $this->info('Invoices marked overdue and penalties applied successfully.');
        return 0;
    }
}
