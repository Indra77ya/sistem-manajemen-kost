<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kost:cancel-expired-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan booking yang sudah melewati batas waktu pembayaran';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredBookings = Booking::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        $count = $expiredBookings->count();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'cancelled']);
            $this->info("Booking #{$booking->id} dibatalkan karena kadaluarsa.");
        }

        $this->info("Total {$count} booking telah dibatalkan.");
    }
}
