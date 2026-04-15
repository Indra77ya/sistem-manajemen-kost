<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'kost:mark-overdue';
    protected $description = 'Mark unpaid invoices as overdue after due date';

    public function handle()
    {
        $count = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$count} invoices as overdue.");
        return 0;
    }
}
