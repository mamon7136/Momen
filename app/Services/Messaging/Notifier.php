<?php

namespace App\Services\Messaging;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Payments\PaymentManager;

/**
 * All customer/merchant notifications in one place (Arabic copy).
 * Messages are queued (database queue + cron on DirectAdmin) so checkout never waits on WhatsApp.
 */
class Notifier
{
    protected function queue(string $to, string $body, ?int $storeId): void
    {
        if ($to) {
            SendWhatsAppMessage::dispatch($to, $body, $storeId);
        }
    }

    public function orderPlaced(Order $order): void
    {
        $store = $order->store;
        $items = $order->items->map(fn ($i) => "• {$i->name} × {$i->quantity}")->implode("\n");
        $body = "مرحباً {$order->customer->name} 👋\nتم استلام طلبك رقم #{$order->number} من {$store->name}\n\n{$items}\n\n"
            .'الإجمالي: '.money($order->total)."\nطريقة الدفع: ".(PaymentManager::availableDrivers()[$order->payment_method] ?? $order->payment_method)
            ."\n\nتتبع طلبك والفاتورة:\n".$order->trackingUrl();
        $this->queue($order->ship_phone ?: $order->customer->phone, $body, $order->store_id);

        $merchant = $store->whatsapp ?: $store->phone;
        $this->queue((string) $merchant, "🛒 طلب جديد #{$order->number}\nالعميل: {$order->customer->name} ({$order->customer->phone})\nالإجمالي: ".money($order->total)
            ."\n".url('/dashboard/orders/'.$order->id), $order->store_id);
    }

    public function statusChanged(Order $order, ?string $note = null): void
    {
        $body = "تحديث طلبك #{$order->number} من {$order->store->name}:\nالحالة: {$order->statusLabel()}"
            .($note ? "\n{$note}" : '')
            .($order->shipment?->driver_phone ? "\nرقم المندوب: {$order->shipment->driver_phone}" : '')
            ."\n".$order->trackingUrl();
        $this->queue($order->ship_phone ?: $order->customer->phone, $body, $order->store_id);
    }

    public function paymentConfirmed(Order $order): void
    {
        $this->queue($order->ship_phone ?: $order->customer->phone,
            "✅ تم تأكيد الدفع لطلبك #{$order->number}. شكراً لك!\n".$order->trackingUrl(), $order->store_id);
    }

    public function paymentRejected(Order $order, string $reason): void
    {
        $this->queue($order->ship_phone ?: $order->customer->phone,
            "⚠️ لم نتمكن من تأكيد إيصال الدفع لطلبك #{$order->number}\nالسبب: {$reason}\nيرجى إعادة رفع الإيصال:\n".$order->trackingUrl(), $order->store_id);
    }

    public function merchantReceiptUploaded(Payment $payment): void
    {
        $order = $payment->order()->withoutGlobalScope('store')->first();
        $flags = $payment->gateway_payload['flags'] ?? [];
        $warn = $flags ? "\n⚠️ تنبيه: ".implode('، ', array_map(fn ($f) => $f === 'duplicate_reference' ? 'رقم العملية مستخدم مسبقاً' : 'الصورة مكررة', $flags)) : '';
        $this->queue((string) ($order->store->whatsapp ?: $order->store->phone),
            "💳 إيصال دفع جديد للطلب #{$order->number}\nالمبلغ: ".money($payment->amount)."\nرقم العملية: {$payment->reference}{$warn}\nراجع: ".url('/dashboard/orders/'.$order->id), $order->store_id);
    }

    public function lowStock(Product $product): void
    {
        $store = $product->store;
        $this->queue((string) ($store->whatsapp ?: $store->phone), "📦 تنبيه مخزون: المنتج \"{$product->name}\" قارب على النفاد (المتبقي {$product->availableStock()}).", $store->id);
    }
}
