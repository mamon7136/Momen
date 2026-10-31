<?php

namespace App\Services\Payments;

use App\Models\StorePaymentMethod;
use Illuminate\Support\Collection;

class PaymentManager
{
    public function driver(StorePaymentMethod $method): PaymentDriver
    {
        $class = config('platform.payment_drivers.'.$method->driver);
        if (! $class) {
            throw new \InvalidArgumentException("Unknown payment driver [{$method->driver}]");
        }

        return new $class($method);
    }

    public function forKey(string $key): ?PaymentDriver
    {
        $method = StorePaymentMethod::where('driver', $key)->where('is_active', true)->first();

        return $method ? $this->driver($method) : null;
    }

    /** @return Collection<int, PaymentDriver> */
    public function available(): Collection
    {
        return StorePaymentMethod::where('is_active', true)->orderBy('sort')->get()
            ->map(fn ($m) => $this->driver($m));
    }

    public static function availableDrivers(): array
    {
        return [
            'cod' => 'الدفع عند الاستلام',
            'mbok' => 'بنكك (mBoK) - بنك الخرطوم',
            'bank_transfer' => 'تحويل بنكي (فوري، أوكاش، بنوك أخرى)',
            'syberpay' => 'SyberPay - بطاقات EBS',
        ];
    }
}
