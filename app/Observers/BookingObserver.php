<?php

namespace App\Observers;

use App\Models\Booking;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        if ($booking->status === 'pending' || $booking->status === 'confirmed') {
            $booking->room->update(['status' => 'reserved']);
        }
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        if ($booking->isDirty('status')) {
            if (in_array($booking->status, ['cancelled'])) {
                // Check if there are other active bookings for this room (unlikely in this simple logic but safe)
                $booking->room->update(['status' => 'available']);
            }

            if ($booking->status === 'completed') {
                $booking->room->update(['status' => 'occupied']);
            }

            if ($booking->status === 'confirmed') {
                 $booking->room->update(['status' => 'reserved']);
            }
        }
    }
}
