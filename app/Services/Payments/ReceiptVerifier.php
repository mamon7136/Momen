<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;

/**
 * Records a manual receipt (MBOK/bank) and runs fraud heuristics:
 *  - same transaction reference already used (any store on the platform)
 *  - identical image already uploaded (sha256 of file)
 * Suspicious receipts are flagged so the merchant sees a warning before approval.
 */
class ReceiptVerifier
{
    public function record(Order $order, string $driver, array $data, UploadedFile $file): Payment
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $path = $file->store('receipts/'.$order->store_id, 'local'); // private disk, never public

        $flags = [];
        $reference = trim($data['reference']);

        if (Payment::withoutGlobalScope('store')->where('driver', $driver)->where('reference', $reference)
            ->whereIn('status', ['review', 'approved'])->exists()) {
            $flags[] = 'duplicate_reference';
        }
        if (Payment::withoutGlobalScope('store')->where('receipt_hash', $hash)->exists()) {
            $flags[] = 'duplicate_image';
        }

        $payment = Payment::create([
            'store_id' => $order->store_id,
            'order_id' => $order->id,
            'driver' => $driver,
            'amount' => $order->cod_deposit > 0 && $order->payment_method === 'cod' ? $order->cod_deposit : $order->total,
            'currency' => $order->currency,
            'status' => 'review',
            'reference' => $reference,
            'sender_name' => $data['sender_name'] ?? null,
            'sender_account' => $data['sender_account'] ?? null,
            'receipt_path' => $path,
            'receipt_hash' => $hash,
            'gateway_payload' => ['flags' => $flags],
        ]);

        $order->update(['payment_status' => 'pending_review', 'status' => $order->status === 'awaiting_payment' ? 'payment_review' : $order->status]);
        $order->history()->create(['store_id' => $order->store_id, 'status' => 'payment_review', 'note' => 'تم رفع إيصال الدفع - رقم العملية '.$reference]);

        app(PaymentService::class)->notifyMerchantOfReceipt($payment);

        return $payment;
    }
}
