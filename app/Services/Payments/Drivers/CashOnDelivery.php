<?php

namespace App\Services\Payments\Drivers;

use App\Services\Payments\PaymentDriver;

/**
 * Enhanced COD: optional deposit (عربون) via MBOK for high-value orders / risky areas,
 * and phone-confirmation before shipping (handled in OrderService + WhatsApp).
 */
class CashOnDelivery extends PaymentDriver
{
    public function key(): string
    {
        return 'cod';
    }

    public function label(): string
    {
        return 'الدفع عند الاستلام';
    }

    public function initialOrderStatus(): string
    {
        return 'pending';
    }

    /** Deposit percentage required on orders above the configured threshold. */
    public function depositFor(float $total): float
    {
        $threshold = (float) $this->config('deposit_threshold', 0);
        $percent = (float) $this->config('deposit_percent', 0);

        return ($threshold > 0 && $percent > 0 && $total >= $threshold) ? round($total * $percent / 100, 2) : 0.0;
    }
}
