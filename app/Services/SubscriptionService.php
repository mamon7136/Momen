<?php

namespace App\Services;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Store;
use App\Models\Subscription;
use Illuminate\Http\UploadedFile;

class SubscriptionService
{
    /** @param int|null $trialDays activate a trial instead of a paid period */
    public function activate(Subscription $sub, ?int $adminId = null, ?int $trialDays = null): Subscription
    {
        $store = $sub->store;
        $base = $store->subscription_ends_at && $store->subscription_ends_at->isFuture() ? $store->subscription_ends_at : now();
        $ends = $trialDays ? now()->addDays($trialDays)
            : ($sub->cycle === 'yearly' ? $base->copy()->addYear() : $base->copy()->addMonth());

        $sub->update(['status' => 'active', 'starts_at' => now(), 'ends_at' => $sub->plan->isFree() ? null : $ends, 'approved_by' => $adminId]);
        $store->update(['status' => 'active', 'plan_id' => $sub->plan_id, 'subscription_ends_at' => $sub->plan->isFree() ? null : $ends]);

        return $sub;
    }

    public function submitReceipt(Subscription $sub, string $method, string $reference, UploadedFile $file): Subscription
    {
        $sub->update([
            'payment_method' => $method,
            'payment_reference' => $reference,
            'receipt_path' => $file->store('subscriptions', 'local'),
        ]);

        return $sub;
    }

    /** Daily cron: expire stores whose period ended (3-day grace), notify before expiry. */
    public function sweep(): int
    {
        $count = 0;
        Store::where('status', 'active')->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<', now()->subDays(3))
            ->each(function (Store $store) use (&$count) {
                $store->update(['status' => 'expired']);
                $count++;
            });

        Store::where('status', 'active')->whereBetween('subscription_ends_at', [now()->addDays(2), now()->addDays(3)])
            ->each(fn (Store $s) => SendWhatsAppMessage::dispatch((string) ($s->whatsapp ?: $s->phone),
                "تنبيه: ينتهي اشتراك متجر {$s->name} بتاريخ {$s->subscription_ends_at->format('Y-m-d')}. جدد الآن: ".url('/dashboard/billing')));

        return $count;
    }
}
