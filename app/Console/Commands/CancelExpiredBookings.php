<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingInvitation;
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
        // 1. Handle expired bookings
        $expiredBookings = Booking::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        $bookingCount = $expiredBookings->count();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'cancelled']);
            $this->info("Booking #{$booking->id} dibatalkan karena kadaluarsa.");
        }

        // 2. Handle expired invitations
        $expiredInvitations = BookingInvitation::whereNull('used_at')
            ->where('expires_at', '<', now())
            ->get();

        $invitationCount = $expiredInvitations->count();

        foreach ($expiredInvitations as $invitation) {
            // Revert room status back to available
            $invitation->room->update(['status' => 'available']);

            // We could delete them or keep them marked. Let's keep them and they won't be picked up next time because we'd need to mark them as processed or delete.
            // For now, let's just delete them to keep the table clean if they are expired and unused.
            $invitation->delete();
            $this->info("Undangan Booking #{$invitation->id} dihapus karena kadaluarsa.");
        }

        $this->info("Selesai: {$bookingCount} booking dibatalkan, {$invitationCount} undangan kadaluarsa dibersihkan.");
    }
}
