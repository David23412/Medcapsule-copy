<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\PaymentLog;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $this->logPaymentAction($payment, 'created', 'Payment created');
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        $changes = $payment->getDirty();
        
        if (!empty($changes)) {
            $this->logPaymentAction($payment, 'updated', 'Payment updated', $changes);
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        $this->logPaymentAction($payment, 'deleted', 'Payment deleted');
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        $this->logPaymentAction($payment, 'restored', 'Payment restored');
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        $this->logPaymentAction($payment, 'force_deleted', 'Payment force deleted');
    }

    /**
     * Log payment action to PaymentLog
     */
    private function logPaymentAction(Payment $payment, string $action, string $description, array $changes = []): void
    {
        try {
            PaymentLog::create([
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'action' => $action,
                'description' => $description,
                'old_status' => $payment->getOriginal('status'),
                'new_status' => $payment->status,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'admin_user_id' => auth()->check() ? auth()->id() : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => !empty($changes) ? json_encode($changes) : null,
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the payment process
            \Log::error('Failed to log payment action: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }
}