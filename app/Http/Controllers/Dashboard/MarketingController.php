<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessage;
use App\Models\AbandonedCart;
use App\Models\Customer;

class MarketingController extends Controller
{
    public function index()
    {
        return view('dashboard.marketing', [
            'carts' => AbandonedCart::whereNotNull('phone')->latest()->limit(50)->get(),
            'topCustomers' => Customer::orderByDesc('total_spent')->limit(10)->get(),
            'storeUrl' => current_store()->primaryUrl(),
        ]);
    }

    public function remind(AbandonedCart $cart)
    {
        $store = current_store();
        SendWhatsAppMessage::dispatch($cart->phone, "مرحباً 👋 لاحظنا أنك تركت منتجات في سلتك في {$store->name}.\nأكمل طلبك الآن: ".rtrim($store->primaryUrl(), '/').'/cart', $store->id);
        $cart->update(['reminded_at' => now()]);

        return back()->with('status', 'تم إرسال التذكير عبر واتساب');
    }
}
