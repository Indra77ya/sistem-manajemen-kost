<?php

use App\Http\Controllers\RoomController;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

Route::get('/', [RoomController::class, 'index']);

Route::get('/invoice/{invoice}/download', function (Invoice $invoice) {
    // Basic authorization check
    if (!Auth::check()) {
        abort(403);
    }

    $user = Auth::user();

    // Tenants can only download their own invoices
    if ($user->role === 'tenant' && $invoice->lease->user_id !== $user->id) {
        abort(403);
    }

    // Admins can only download invoices from their branches
    if ($user->role === 'admin') {
        $branchIds = $user->branches()->pluck('branches.id')->toArray();
        if (!in_array($invoice->branch_id, $branchIds)) {
            abort(403);
        }
    }

    $pdf = Pdf::loadView('invoice-pdf', ['invoice' => $invoice]);
    return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
})->name('invoice.download')->middleware(['web', 'auth']);
