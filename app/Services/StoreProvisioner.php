<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Plan;
use App\Models\ShippingZone;
use App\Models\Store;
use App\Models\StorePaymentMethod;
use App\Models\Subscription;
use App\Models\Theme;
use App\Models\User;
use App\Support\Tenancy\CurrentStore;
use Illuminate\Support\Facades\DB;

/** Creates a fully working store in one transaction: default branch, payment methods, shipping, wallet, subscription. */
class StoreProvisioner
{
    public function __construct(protected CurrentStore $tenancy, protected WalletService $wallets) {}

    public function provision(User $owner, array $data, Plan $plan, string $cycle = 'monthly', ?Theme $theme = null): Store
    {
        return DB::transaction(function () use ($owner, $data, $plan, $cycle, $theme) {
            $store = Store::create([
                'owner_id' => $owner->id,
                'plan_id' => $plan->id,
                'theme_id' => $theme?->id ?? Theme::where('is_active', true)->value('id'),
                'slug' => strtolower($data['slug']),
                'name' => $data['store_name'],
                'phone' => $data['phone'] ?? $owner->phone,
                'whatsapp' => $data['phone'] ?? $owner->phone,
                'state' => $data['state'] ?? 'الخرطوم',
                'city' => $data['city'] ?? 'الخرطوم',
                'status' => 'pending',
            ]);
            $store->staff()->attach($owner->id, ['role' => 'owner']);

            $this->tenancy->runAs($store, function (Store $store) {
                Branch::create(['name' => 'الفرع الرئيسي', 'state' => $store->state, 'city' => $store->city, 'is_default' => true]);
                StorePaymentMethod::create(['driver' => 'cod', 'label' => 'الدفع عند الاستلام', 'sort' => 1]);
                StorePaymentMethod::create(['driver' => 'mbok', 'label' => 'بنكك (mBoK)', 'is_active' => false, 'sort' => 2,
                    'config' => ['account_number' => '', 'account_name' => '']]);
                ShippingZone::create(['state' => 'الخرطوم', 'price' => 3000, 'eta' => 'خلال 24 ساعة']);
            });
            $this->wallets->for($store);

            $amount = $plan->priceFor($cycle);
            $sub = Subscription::create([
                'store_id' => $store->id, 'plan_id' => $plan->id, 'cycle' => $cycle, 'amount' => $amount,
                'status' => 'pending',
            ]);

            if ($amount <= 0 || $plan->trial_days > 0) {
                app(SubscriptionService::class)->activate($sub, null, $plan->trial_days > 0 && $amount > 0 ? $plan->trial_days : null);
            }

            return $store->fresh();
        });
    }
}
