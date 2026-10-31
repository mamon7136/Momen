<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceApp;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        $store = current_store();

        return view('dashboard.billing', [
            'plans' => Plan::where('is_active', true)->orderBy('sort')->get(),
            'subscriptions' => $store->subscriptions()->with('plan')->get(),
            'pending' => $store->subscriptions()->where('status', 'pending')->first(),
            'billing' => config('platform.billing'),
        ]);
    }

    public function renew(Request $request, SubscriptionService $service)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id', 'cycle' => 'required|in:monthly,yearly',
            'method' => 'required|in:mbok,bank_transfer', 'reference' => 'required|string|max:64', 'receipt' => 'required|image|max:4096',
        ], [], ['reference' => 'رقم العملية', 'receipt' => 'صورة الإشعار']);

        $store = current_store();
        $plan = Plan::find($data['plan_id']);
        $sub = $store->subscriptions()->where('status', 'pending')->first()
            ?? Subscription::create(['store_id' => $store->id, 'plan_id' => $plan->id, 'cycle' => $data['cycle'], 'amount' => $plan->priceFor($data['cycle'])]);
        $sub->update(['plan_id' => $plan->id, 'cycle' => $data['cycle'], 'amount' => $plan->priceFor($data['cycle'])]);
        $service->submitReceipt($sub, $data['method'], $data['reference'], $request->file('receipt'));

        return back()->with('status', 'تم استلام إيصال الاشتراك، سيتم التفعيل بعد المراجعة (عادة خلال ساعة)');
    }

    public function apps()
    {
        return view('dashboard.apps', [
            'apps' => MarketplaceApp::where('is_active', true)->get()->groupBy('category'),
            'enabled' => DB::table('store_apps')->where('store_id', current_store()->id)->where('is_enabled', true)->pluck('app_id')->all(),
        ]);
    }

    public function toggleApp(MarketplaceApp $app)
    {
        $q = DB::table('store_apps')->where('store_id', current_store()->id)->where('app_id', $app->id);
        $row = $q->first();
        $row ? $q->update(['is_enabled' => ! $row->is_enabled, 'updated_at' => now()])
            : DB::table('store_apps')->insert(['store_id' => current_store()->id, 'app_id' => $app->id, 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('status', 'تم تحديث التطبيق');
    }
}
