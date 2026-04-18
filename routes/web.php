<?php

use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

Route::get('/', [RoomController::class, 'index']);

Route::middleware(['auth'])->group(function () {
    Route::get('/booking/{room}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/{room}', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking-success/{booking}', [BookingController::class, 'success'])->name('booking.success');
});

Route::get('/invoice/{invoice}/download', function (Invoice $invoice) {
    // Basic authorization check
    if (!Auth::check()) {
        abort(403);
    }

    $user = Auth::user();

    // Tenants can only download their own invoices
    if ($user->hasRole('tenant') && $invoice->lease->user_id !== $user->id) {
        abort(403);
    }

    // Admins can only download invoices from their branches
    if ($user->hasRole('admin_cabang')) {
        $branchIds = $user->branches()->pluck('branches.id')->toArray();
        if (!in_array($invoice->branch_id, $branchIds)) {
            abort(403);
        }
    }

    $pdf = Pdf::loadView('invoice-pdf', ['invoice' => $invoice]);
    return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
})->name('invoice.download')->middleware(['web', 'auth']);
