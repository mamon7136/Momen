<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Messaging\WhatsApp;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;

/** Public order page: status timeline, invoice, payment instructions and receipt upload. */
class TrackingController extends Controller
{
    public function lookup(Request $request)
    {
        if ($request->filled(['number', 'phone'])) {
            $order = Order::where('number', $request->number)->where('ship_phone', WhatsApp::normalize($request->phone))->first();
            if ($order) {
                return redirect(sroute('track', ['token' => $order->tracking_token]));
            }
            session()->flash('error', 'لم يتم العثور على الطلب');
        }

        return view('storefront.track-lookup');
    }

    public function show(string $token, PaymentManager $payments)
    {
        $order = Order::with('items', 'history', 'payments', 'shipment.company', 'branch')->where('tracking_token', $token)->firstOrFail();

        return view('storefront.track', ['order' => $order, 'driver' => $payments->forKey($order->payment_method)]);
    }

    public function receipt(Request $request, string $token, PaymentManager $payments)
    {
        $order = Order::where('tracking_token', $token)->firstOrFail();
        if ($order->payment_status === 'paid') {
            return back()->with('status', 'الطلب مدفوع بالفعل');
        }

        // COD orders with deposit pay the deposit through the store's MBOK/bank method
        $key = $order->payment_method === 'cod' ? $request->input('via', 'mbok') : $order->payment_method;
        $driver = $payments->forKey($key);
        abort_unless($driver && $driver->requiresProof(), 422);
        $driver->submitProof($order, $request);

        return back()->with('status', 'تم استلام الإيصال ✅ سيتم تأكيد الدفع قريباً وإشعارك عبر واتساب');
    }
}
