<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentDriver;
use App\Services\Payments\ReceiptVerifier;
use Illuminate\Http\Request;

class BankTransfer extends PaymentDriver
{
    public function key(): string
    {
        return 'bank_transfer';
    }

    public function label(): string
    {
        return 'تحويل بنكي';
    }

    public function requiresProof(): bool
    {
        return true;
    }

    public function submitProof(Order $order, Request $request): Payment
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:64'],
            'sender_name' => ['nullable', 'string', 'max:120'],
            'sender_account' => ['nullable', 'string', 'max:40'],
            'receipt' => ['required', 'image', 'max:4096'],
        ], [], ['reference' => 'رقم العملية', 'receipt' => 'صورة الإشعار']);

        return app(ReceiptVerifier::class)->record($order, $this->key(), $data, $request->file('receipt'));
    }
}
