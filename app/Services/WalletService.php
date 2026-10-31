<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Store;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * Merchant wallet ledger. Every movement is an immutable wallet_transactions row with balance_after.
 * Sale  => +amount, Commission => -commission (platform revenue), Payout => -amount.
 *
 * Note: for MBOK/bank/COD the money lands directly in the merchant's account, so the sale credit is
 * immediately offset by a "collected_directly" debit and only the commission remains as a receivable
 * (negative balance = merchant owes the platform). For platform-collected gateways (SyberPay on the
 * platform account) the merchant balance stays positive and is paid out.
 */
class WalletService
{
    public function for(Store|int $store): Wallet
    {
        $id = $store instanceof Store ? $store->id : $store;

        return Wallet::firstOrCreate(['store_id' => $id], ['balance' => 0, 'currency' => 'SDG']);
    }

    public function record(Store|int $store, string $type, float $amount, ?string $description = null, $reference = null): void
    {
        DB::transaction(function () use ($store, $type, $amount, $description, $reference) {
            $wallet = Wallet::lockForUpdate()->find($this->for($store)->id);
            $wallet->balance = round((float) $wallet->balance + $amount, 2);
            $wallet->save();
            $wallet->transactions()->create([
                'store_id' => $wallet->store_id, 'type' => $type, 'amount' => $amount, 'balance_after' => $wallet->balance,
                'description' => $description,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
            ]);
        });
    }

    public function creditSale(Order $order, float $amount): void
    {
        $platformCollected = in_array($order->payment_method, ['syberpay'], true);
        $rate = (float) ($order->store->plan?->commission_rate ?? 0);
        $commission = round((float) $order->total * $rate / 100, 2);
        $order->update(['commission' => $commission]);

        $this->record($order->store_id, 'sale', $amount, 'مبيعات الطلب #'.$order->number, $order);
        if (! $platformCollected) {
            $this->record($order->store_id, 'collected_directly', -$amount, 'تم تحصيل المبلغ مباشرة بواسطة التاجر', $order);
        }
        if ($commission > 0) {
            $this->record($order->store_id, 'commission', -$commission, 'عمولة المنصة '.$rate.'% على الطلب #'.$order->number, $order);
        }
    }

    public function requestPayout(Store $store, float $amount, string $method, string $account): Payout
    {
        $wallet = $this->for($store);
        if ($amount <= 0 || $amount > (float) $wallet->balance) {
            throw new \DomainException('الرصيد غير كافٍ');
        }

        return Payout::create(['store_id' => $store->id, 'amount' => $amount, 'method' => $method, 'account_details' => $account]);
    }

    public function markPayoutPaid(Payout $payout, int $adminId, ?string $reference): void
    {
        DB::transaction(function () use ($payout, $adminId, $reference) {
            $payout->update(['status' => 'paid', 'processed_by' => $adminId, 'processed_at' => now(), 'reference' => $reference]);
            $this->record($payout->store_id, 'payout', -(float) $payout->amount, 'تحويل رصيد للتاجر', $payout);
        });
    }
}
