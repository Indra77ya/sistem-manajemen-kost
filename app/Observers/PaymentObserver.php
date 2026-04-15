<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        if ($payment->isDirty('status') && $payment->status === 'verified') {
            $invoice = $payment->invoice;
            if ($invoice) {
                $invoice->update(['status' => 'paid']);
            }
        }
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        if ($payment->status === 'verified') {
            $invoice = $payment->invoice;
            if ($invoice) {
                $invoice->update(['status' => 'paid']);
            }
        }
    }
}
