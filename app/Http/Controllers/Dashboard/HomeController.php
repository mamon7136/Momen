<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\Store;
use App\Models\StorePaymentMethod;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $today = Order::whereDate('created_at', today())->where('status', '!=', 'cancelled');
        $month = Order::where('created_at', '>=', now()->startOfMonth())->where('status', '!=', 'cancelled');

        return view('dashboard.home', [
            'stats' => [
                'today_orders' => (clone $today)->count(),
                'today_sales' => (clone $today)->sum('total'),
                'month_sales' => (clone $month)->sum('total'),
                'month_orders' => (clone $month)->count(),
                'pending' => Order::whereIn('status', ['pending', 'payment_review'])->count(),
                'receipts' => Payment::where('status', 'review')->count(),
            ],
            'latest' => Order::with('customer')->latest()->limit(8)->get(),
            'lowStock' => Product::where('track_stock', true)->get()->filter->isLowStock()->take(6),
            'checklist' => $this->checklist(current_store()),
        ]);
    }

    protected function checklist(Store $store): array
    {
        return [
            ['label' => 'أضف أول منتج', 'done' => Product::exists(), 'url' => route('dashboard.products.create')],
            ['label' => 'فعّل وسيلة دفع (بنكك)', 'done' => StorePaymentMethod::where('driver', '!=', 'cod')->where('is_active', true)->exists(), 'url' => route('dashboard.payment-methods.index')],
            ['label' => 'حدد مناطق وأسعار التوصيل', 'done' => ShippingZone::count() > 1, 'url' => route('dashboard.shipping.index')],
            ['label' => 'خصص تصميم متجرك', 'done' => ! empty($store->theme_settings), 'url' => route('dashboard.design.edit')],
        ];
    }

    public function switch(Request $request, Store $store)
    {
        abort_unless($request->user()->roleIn($store), 403);
        session(['active_store_id' => $store->id]);

        return redirect()->route('dashboard.home');
    }
}
