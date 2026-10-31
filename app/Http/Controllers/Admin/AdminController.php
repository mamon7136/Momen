<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Plan;
use App\Models\ShippingCompany;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\Theme;
use App\Services\SubscriptionService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::withoutGlobalScope('store');

        return view('admin.home', ['stats' => [
            'stores' => Store::count(),
            'active' => Store::where('status', 'active')->count(),
            'pending_subs' => Subscription::where('status', 'pending')->whereNotNull('receipt_path')->count(),
            'gmv_month' => (clone $orders)->where('created_at', '>=', now()->startOfMonth())->where('status', '!=', 'cancelled')->sum('total'),
            'commission_month' => (clone $orders)->where('created_at', '>=', now()->startOfMonth())->sum('commission'),
            'mrr' => Subscription::where('status', 'active')->where('ends_at', '>', now())->get()->sum(fn ($s) => $s->cycle === 'yearly' ? $s->amount / 12 : $s->amount),
            'payouts' => Payout::where('status', 'requested')->count(),
        ], 'latestStores' => Store::with('plan', 'owner')->latest()->limit(10)->get()]);
    }

    public function stores(Request $request)
    {
        $stores = Store::with('plan', 'owner', 'wallet')
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('slug', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.stores', compact('stores'));
    }

    public function storeStatus(Request $request, Store $store)
    {
        $store->update($request->validate(['status' => 'required|in:active,suspended,pending,expired']));

        return back()->with('status', 'تم تحديث حالة المتجر');
    }

    public function subscriptions()
    {
        return view('admin.subscriptions', ['subscriptions' => Subscription::with('store', 'plan')->latest()->paginate(30)]);
    }

    public function approveSubscription(Request $request, Subscription $subscription, SubscriptionService $service)
    {
        $service->activate($subscription, $request->user()->id);

        return back()->with('status', 'تم تفعيل الاشتراك');
    }

    public function subscriptionReceipt(Subscription $subscription)
    {
        abort_unless($subscription->receipt_path, 404);

        return Storage::disk('local')->response($subscription->receipt_path);
    }

    public function plans()
    {
        return view('admin.plans', ['plans' => Plan::orderBy('sort')->get()]);
    }

    public function savePlan(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:plans,id', 'slug' => 'required|alpha_dash', 'name' => 'required|string', 'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0', 'price_yearly' => 'required|numeric|min:0', 'commission_rate' => 'required|numeric|min:0|max:30',
            'max_products' => 'nullable|integer', 'max_staff' => 'nullable|integer', 'max_branches' => 'nullable|integer',
            'trial_days' => 'nullable|integer|min:0', 'features' => 'nullable|string', 'sort' => 'nullable|integer',
        ]);
        $data['features'] = array_values(array_filter(array_map('trim', explode("\n", $data['features'] ?? ''))));
        $data['custom_domain'] = $request->boolean('custom_domain');
        $data['is_active'] = $request->boolean('is_active');
        $data['trial_days'] ??= 0;
        Plan::updateOrCreate(['id' => $data['id'] ?? null], collect($data)->except('id')->all());

        return back()->with('status', 'تم حفظ الباقة');
    }

    public function themes()
    {
        return view('admin.themes', ['themes' => Theme::all()]);
    }

    public function saveTheme(Request $request)
    {
        $data = $request->validate(['slug' => 'required|alpha_dash', 'name' => 'required|string', 'primary' => 'required|regex:/^#[0-9a-fA-F]{6}$/', 'secondary' => 'required|regex:/^#[0-9a-fA-F]{6}$/']);
        Theme::updateOrCreate(['slug' => $data['slug']], ['name' => $data['name'], 'is_active' => true,
            'default_settings' => ['colors' => ['primary' => $data['primary'], 'secondary' => $data['secondary']]]]);

        return back()->with('status', 'تم حفظ الثيم');
    }

    public function payouts()
    {
        return view('admin.payouts', ['payouts' => Payout::with('store')->latest()->paginate(30)]);
    }

    public function payoutPaid(Request $request, Payout $payout, WalletService $wallets)
    {
        abort_unless($payout->status === 'requested', 422);
        $wallets->markPayoutPaid($payout, $request->user()->id, $request->input('reference'));

        return back()->with('status', 'تم تسجيل التحويل');
    }

    public function settings()
    {
        return view('admin.settings', ['rates' => ExchangeRate::all(), 'companies' => ShippingCompany::whereNull('store_id')->get()]);
    }

    public function saveRate(Request $request)
    {
        $data = $request->validate(['currency' => 'required|alpha|size:3', 'rate_to_sdg' => 'required|numeric|min:0.0001']);
        ExchangeRate::updateOrCreate(['currency' => strtoupper($data['currency'])], ['rate_to_sdg' => $data['rate_to_sdg']]);

        return back()->with('status', 'تم تحديث سعر الصرف');
    }

    public function saveCompany(Request $request)
    {
        $data = $request->validate(['name' => 'required|string', 'type' => 'required|in:courier,motorcycle,rickshaw,bus_cargo', 'tracking_url' => 'nullable|url']);
        ShippingCompany::create($data);

        return back()->with('status', 'تمت إضافة شركة الشحن للمنصة');
    }
}
