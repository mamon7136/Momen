<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Messaging\Notifier;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(protected WalletService $wallets, protected Notifier $notifier) {}

    public function approve(Payment $payment, ?int $userId = null, ?string $note = null): Payment
    {
        return DB::transaction(function () use ($payment, $userId, $note) {
            $payment = Payment::withoutGlobalScope('store')->lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'approved') {
                return $payment; // idempotent (double click / repeated gateway callback)
            }

            $payment->update(['status' => 'approved', 'reviewed_by' => $userId, 'reviewed_at' => now(), 'review_note' => $note]);
            $order = $payment->order()->withoutGlobalScope('store')->first();

            $isDeposit = $order->payment_method === 'cod' && (float) $payment->amount < (float) $order->total;
            if (! $isDeposit) {
                $order->update(['payment_status' => 'paid']);
                $this->wallets->creditSale($order, (float) $payment->amount);
            }
            if (in_array($order->status, ['pending', 'awaiting_payment', 'payment_review'])) {
                $order->update(['status' => 'confirmed']);
            }
            $order->history()->create(['store_id' => $order->store_id, 'status' => 'confirmed',
                'note' => $isDeposit ? 'تم تأكيد العربون' : 'تم تأكيد الدفع', 'user_id' => $userId]);

            $this->notifier->paymentConfirmed($order);

            return $payment;
        });
    }

    public function reject(Payment $payment, ?int $userId, string $reason): Payment
    {
        $payment->update(['status' => 'rejected', 'reviewed_by' => $userId, 'reviewed_at' => now(), 'review_note' => $reason]);
        $order = $payment->order;
        $order->update(['payment_status' => 'unpaid', 'status' => 'awaiting_payment']);
        $order->history()->create(['store_id' => $order->store_id, 'status' => 'awaiting_payment', 'note' => 'رفض الإيصال: '.$reason, 'user_id' => $userId]);
        $this->notifier->paymentRejected($order, $reason);

        return $payment;
    }

    public function notifyMerchantOfReceipt(Payment $payment): void
    {
        $this->notifier->merchantReceiptUploaded($payment);
    }
}
