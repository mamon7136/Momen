<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentDriver;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * SyberPay hosted checkout (supports EBS cards / national switch).
 * NOTE: endpoint names and the hash recipe must be matched to the spec SyberPay hands over
 * at merchant onboarding (they differ per integration version). Credentials are per-store
 * (store_payment_methods.config) falling back to platform-level config/messaging.php.
 */
class SyberPay extends PaymentDriver
{
    public function key(): string
    {
        return 'syberpay';
    }

    public function label(): string
    {
        return 'بطاقة بنكية (SyberPay)';
    }

    public function isOnline(): bool
    {
        return true;
    }

    protected function cred(string $key): ?string
    {
        return $this->config($key) ?? config('messaging.syberpay.'.$key);
    }

    public function initiate(Order $order): ?string
    {
        $payment = Payment::create([
            'store_id' => $order->store_id, 'order_id' => $order->id, 'driver' => $this->key(),
            'amount' => $order->total, 'currency' => 'SDG', 'status' => 'pending',
        ]);

        $customerRef = 'P'.$payment->id;
        $amount = number_format((float) $order->total, 2, '.', '');
        $hash = hash('sha256', $this->cred('application_id').'|'.$this->cred('service_id').'|'.$customerRef.'|'.$amount.'|SDG|'.$this->cred('salt'));

        $response = Http::timeout(20)->post(rtrim($this->cred('base_url'), '/').'/getUrl', [
            'applicationId' => $this->cred('application_id'),
            'serviceId' => $this->cred('service_id'),
            'customerRef' => $customerRef,
            'amount' => $amount,
            'currency' => 'SDG',
            'paymentInfo' => ['orderNumber' => $order->number],
            'hash' => $hash,
        ]);

        $payment->update(['gateway_payload' => $response->json(), 'reference' => $response->json('transactionId')]);

        return $response->successful() ? $response->json('paymentUrl') : null;
    }

    public function callback(Request $request): ?Payment
    {
        $ref = (string) $request->input('customerRef');
        $payment = Payment::withoutGlobalScope('store')->find((int) ltrim($ref, 'P'));
        if (! $payment) {
            return null;
        }

        // Never trust the redirect: re-query the gateway for the final status.
        $status = Http::timeout(20)->post(rtrim($this->cred('base_url'), '/').'/payment_status', [
            'applicationId' => $this->cred('application_id'),
            'transactionId' => $payment->reference,
            'hash' => hash('sha256', $this->cred('application_id').'|'.$payment->reference.'|'.$this->cred('salt')),
        ]);

        $payment->update(['gateway_payload' => $status->json()]);
        if (strtolower((string) $status->json('status')) === 'successful') {
            app(PaymentService::class)->approve($payment, null, 'SyberPay auto');
        } else {
            $payment->update(['status' => 'failed']);
        }

        return $payment;
    }
}
