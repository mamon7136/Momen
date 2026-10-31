<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingZone;
use App\Services\Messaging\Notifier;
use App\Services\Messaging\WhatsApp;
use App\Services\Payments\Drivers\CashOnDelivery;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected ShippingCalculator $shipping,
        protected InventoryService $inventory,
        protected PaymentManager $payments,
        protected WalletService $wallets,
        protected Notifier $notifier,
    ) {}

    /**
     * Price a checkout without persisting anything (used by the live checkout summary and by place()).
     *
     * @return array{subtotal:float, discount:float, shipping:float, payment_fee:float, total:float, zone:?ShippingZone, coupon:?Coupon, errors:array}
     */
    public function quote(Collection $lines, array $data, ?Customer $customer = null): array
    {
        $errors = [];
        $subtotal = round($lines->sum('total'), 2);
        $weight = $lines->sum(fn ($l) => (float) $l['product']->weight_kg * $l['qty']);

        $zone = null;
        $shippingFee = 0.0;
        if (($data['fulfillment'] ?? 'delivery') === 'delivery') {
            $zone = $this->shipping->zoneFor($data['state'] ?? '', $data['city'] ?? null, $data['area'] ?? null);
            if (! $zone) {
                $errors['state'] = 'عذراً، لا يتوفر توصيل لهذه المنطقة حالياً';
            } else {
                $shippingFee = $this->shipping->quote($zone, $subtotal, $weight);
            }
        }

        $coupon = null;
        $discount = 0.0;
        if (! empty($data['coupon'])) {
            $coupon = Coupon::where('code', strtoupper(trim($data['coupon'])))->first();
            $error = $coupon ? $coupon->validationError($subtotal, $customer) : 'كود الخصم غير صحيح';
            if ($error) {
                $errors['coupon'] = $error;
                $coupon = null;
            } else {
                $discount = $coupon->discountFor($subtotal);
                if ($coupon->type === 'free_shipping') {
                    $shippingFee = 0.0;
                }
            }
        }

        $paymentFee = 0.0;
        if (! empty($data['payment_method']) && ($driver = $this->payments->forKey($data['payment_method']))) {
            $paymentFee = round(($subtotal - $discount) * (float) ($driver->config('fee_percent') ?? 0) / 100, 2);
            if ($data['payment_method'] === 'cod' && $zone && ! $zone->cod_allowed) {
                $errors['payment_method'] = 'الدفع عند الاستلام غير متاح لهذه المنطقة';
            }
        }

        $total = round(max(0, $subtotal - $discount) + $shippingFee + $paymentFee, 2);

        return ['subtotal' => $subtotal, 'discount' => $discount, 'shipping' => $shippingFee, 'payment_fee' => $paymentFee,
            'total' => $total, 'zone' => $zone, 'coupon' => $coupon, 'errors' => $errors];
    }

    /**
     * @return array{0: Order, 1: ?string} order + optional gateway redirect URL
     */
    public function place(Collection $lines, array $data): array
    {
        if ($lines->isEmpty()) {
            throw new \DomainException('السلة فارغة');
        }
        $store = current_store();
        $driver = $this->payments->forKey($data['payment_method']) ?? throw new \DomainException('طريقة الدفع غير متاحة');

        $order = DB::transaction(function () use ($lines, $data, $store, $driver) {
            $customer = Customer::firstOrCreate(['phone' => WhatsApp::normalize($data['phone'])], ['name' => $data['name']]);
            $quote = $this->quote($lines, $data, $customer);
            if ($quote['errors']) {
                throw new CheckoutException($quote['errors']);
            }

            $deposit = $driver instanceof CashOnDelivery ? $driver->depositFor($quote['total']) : 0.0;

            $order = Order::create([
                'customer_id' => $customer->id,
                'number' => $store->nextOrderNumber(),
                'tracking_token' => Str::random(32),
                'status' => $deposit > 0 ? 'awaiting_payment' : $driver->initialOrderStatus(),
                'payment_method' => $driver->key(),
                'fulfillment' => $data['fulfillment'] ?? 'delivery',
                'branch_id' => $data['branch_id'] ?? null,
                'shipping_zone_id' => $quote['zone']?->id,
                'ship_name' => $data['name'],
                'ship_phone' => WhatsApp::normalize($data['phone']),
                'ship_state' => $data['state'] ?? null,
                'ship_city' => $data['city'] ?? null,
                'ship_area' => $data['area'] ?? null,
                'ship_address' => $data['address'] ?? null,
                'ship_landmark' => $data['landmark'] ?? null,
                'subtotal' => $quote['subtotal'],
                'discount' => $quote['discount'],
                'shipping_fee' => $quote['shipping'],
                'payment_fee' => $quote['payment_fee'],
                'total' => $quote['total'],
                'cod_deposit' => $deposit,
                'coupon_id' => $quote['coupon']?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'store_id' => $store->id,
                    'product_id' => $line['product']->id,
                    'variant_id' => $line['variant']?->id,
                    'name' => $line['product']->name,
                    'options' => $line['variant']?->options,
                    'quantity' => $line['qty'],
                    'unit_price' => $line['price'],
                    'unit_cost' => $line['product']->cost,
                    'total' => $line['total'],
                ]);
                $this->inventory->deduct($line['product'], $line['variant']?->id, $line['qty'], $order, $order->branch_id);
                $line['product']->increment('sales_count', $line['qty']);
            }

            $quote['coupon']?->increment('used_count');
            $customer->increment('orders_count');
            $order->history()->create(['store_id' => $store->id, 'status' => $order->status, 'note' => 'تم إنشاء الطلب']);

            return $order;
        });

        $order->load('items', 'customer', 'store');
        $this->notifier->orderPlaced($order);
        foreach ($order->items as $item) {
            if ($item->product?->isLowStock()) {
                $this->notifier->lowStock($item->product);
            }
        }

        return [$order, $driver->initiate($order)];
    }

    public function updateStatus(Order $order, string $status, ?string $note = null, ?int $userId = null, bool $notify = true): Order
    {
        if (! array_key_exists($status, Order::STATUSES)) {
            throw new \InvalidArgumentException('Invalid status');
        }

        DB::transaction(function () use ($order, $status, $note, $userId) {
            $previous = $order->status;
            $order->update(['status' => $status]);
            $order->history()->create(['store_id' => $order->store_id, 'status' => $status, 'note' => $note, 'user_id' => $userId]);

            if (in_array($status, ['cancelled', 'returned']) && ! in_array($previous, ['cancelled', 'returned'])) {
                $this->inventory->restore($order);
            }

            // COD: cash is collected on delivery => mark paid and post to ledger
            if ($status === 'delivered' && $order->payment_status !== 'paid' && $order->payment_method === 'cod') {
                $order->update(['payment_status' => 'paid']);
                $this->wallets->creditSale($order, (float) $order->total);
                $order->customer->increment('total_spent', (float) $order->total);
            }
            if ($status === 'delivered') {
                $order->shipment?->update(['status' => 'delivered', 'delivered_at' => now()]);
            }
        });

        if ($notify) {
            $this->notifier->statusChanged($order->fresh(['store', 'customer', 'shipment']), $note);
        }

        return $order;
    }

    public function createShipment(Order $order, array $data): Shipment
    {
        $shipment = $order->shipment()->create([
            'store_id' => $order->store_id,
            'shipping_company_id' => $data['shipping_company_id'] ?? $order->shippingZone?->shipping_company_id,
            'waybill_number' => 'WB'.$order->store_id.'-'.$order->number.'-'.strtoupper(Str::random(4)),
            'tracking_number' => $data['tracking_number'] ?? null,
            'driver_name' => $data['driver_name'] ?? null,
            'driver_phone' => $data['driver_phone'] ?? null,
            'cod_amount' => $order->payment_status === 'paid' ? 0 : max(0, (float) $order->total - (float) $order->cod_deposit),
        ]);
        $this->updateStatus($order, 'shipped', 'تم تسليم الطلب لشركة الشحن', $data['user_id'] ?? null);

        return $shipment;
    }
}
