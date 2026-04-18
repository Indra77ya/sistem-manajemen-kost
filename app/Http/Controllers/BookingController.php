<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function create(Room $room)
    {
        if ($room->status !== 'available') {
            return redirect('/')->with('error', 'Kamar tidak tersedia untuk booking.');
        }

        $room->load('branch');

        return view('booking.create', compact('room'));
    }

    public function store(Request $request, Room $room)
    {
        $request->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
        ]);

        if ($room->status !== 'available') {
            return redirect('/')->with('error', 'Kamar baru saja dipesan oleh orang lain.');
        }

        $branch = $room->branch()->first();

        $booking = Booking::create([
            'branch_id' => $room->branch_id,
            'room_id' => $room->id,
            'user_id' => Auth::id(),
            'check_in_date' => $request->check_in_date,
            'booking_fee' => $branch->default_booking_fee ?? 0,
            'status' => 'pending',
            'expires_at' => now()->addHours($branch->booking_expiration_hours ?? 24),
        ]);

        return redirect()->route('booking.success', $booking);
    }

    public function success(Booking $booking)
    {
        // Ensure user can only see their own booking
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        return view('booking.success', compact('booking'));
    }
}
