<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\Store;
use App\Models\StorePaymentMethod;
use App\Services\VisitTracker;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $store = current_store();
        $start = now()->startOfMonth();
        $month = Order::where('created_at', '>=', $start)->whereNotIn('status', ['cancelled', 'returned']);

        $daily = (clone $month)->selectRaw('DATE(created_at) d, SUM(total) t, COUNT(*) c')->groupBy('d')->pluck('t', 'd');
        $days = collect(range(1, now()->daysInMonth))->map(function ($day) use ($start, $daily) {
            $date = $start->copy()->day($day)->toDateString();

            return ['day' => $day, 'date' => $date, 'total' => (float) ($daily[$date] ?? 0)];
        });

        $sales = (float) (clone $month)->sum('total');
        $goal = (float) $store->setting('monthly_goal', 0);

        $products = Product::where('track_stock', true)->where('type', '!=', 'digital')->get();

        return view('dashboard.home', [
            'kpi' => [
                'visits' => VisitTracker::visits($store->id, $start, now()),
                'sales' => $sales,
                'orders' => (clone $month)->count(),
                'goal' => $goal,
                'goal_pct' => $goal > 0 ? min(100, round($sales / $goal * 100)) : 0,
            ],
            'days' => $days,
            'latest' => Order::with('customer')->latest()->limit(6)->get(),
            'activity' => OrderStatusHistory::with('order')->latest('id')->limit(12)->get(),
            'receipts' => Payment::with('order')->where('status', 'review')->latest()->get(),
            'outOfStock' => $products->filter(fn ($p) => $p->availableStock() <= 0)->take(8),
            'lowStock' => $products->filter(fn ($p) => $p->availableStock() > 0 && $p->isLowStock())->take(8),
            'checklist' => $this->checklist($store),
        ]);
    }

    public function goal(Request $request)
    {
        $data = $request->validate(['goal' => 'required|numeric|min:0']);
        $store = current_store();
        $store->update(['settings' => array_merge($store->settings ?? [], ['monthly_goal' => $data['goal']])]);

        return back()->with('status', 'تم تحديد هدف الشهر');
    }

    protected function checklist(Store $store): array
    {
        return [
            ['label' => 'أضف أول منتج', 'done' => Product::exists(), 'url' => route('dashboard.products.create'), 'icon' => 'shirt'],
            ['label' => 'فعّل وسيلة دفع إلكترونية (بنكك)', 'done' => StorePaymentMethod::where('driver', '!=', 'cod')->where('is_active', true)->exists(), 'url' => route('dashboard.payment-methods.index'), 'icon' => 'card'],
            ['label' => 'حدد مناطق وأسعار التوصيل', 'done' => ShippingZone::count() > 1, 'url' => route('dashboard.shipping.index'), 'icon' => 'truck'],
            ['label' => 'اختر ثيم متجرك', 'done' => ! empty($store->theme_settings) || $store->theme_id, 'url' => route('dashboard.themes.index'), 'icon' => 'layout'],
        ];
    }

    public function switch(Request $request, Store $store)
    {
        abort_unless($request->user()->roleIn($store), 403);
        session(['active_store_id' => $store->id]);

        return redirect()->route('dashboard.home');
    }
}
