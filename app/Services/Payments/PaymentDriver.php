<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Models\StorePaymentMethod;
use Illuminate\Http\Request;

/**
 * Contract every payment method implements.
 * - Offline drivers (COD, bank transfer, MBOK receipt) return null from redirect()
 *   and collect proof via submitProof().
 * - Online drivers (SyberPay, ...) return a hosted checkout URL and confirm via callback().
 */
abstract class PaymentDriver
{
    public function __construct(protected StorePaymentMethod $method) {}

    abstract public function key(): string;

    abstract public function label(): string;

    /** Does this method require the customer to upload a receipt / reference? */
    public function requiresProof(): bool
    {
        return false;
    }

    public function isOnline(): bool
    {
        return false;
    }

    /** Called right after an order is placed. Returns a redirect URL for online gateways. */
    public function initiate(Order $order): ?string
    {
        return null;
    }

    /** Customer submits receipt image + transaction reference. */
    public function submitProof(Order $order, Request $request): Payment
    {
        throw new \LogicException('This payment method does not accept receipts.');
    }

    /** Gateway server-to-server callback. */
    public function callback(Request $request): ?Payment
    {
        return null;
    }

    public function instructions(): ?string
    {
        return $this->method->instructions;
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return data_get($this->method->config, $key, $default);
    }

    public function initialOrderStatus(): string
    {
        return 'awaiting_payment';
    }
}
