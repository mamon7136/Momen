<?php

namespace App\Http\Controllers\Storefront;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Branch;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\Messaging\WhatsApp;
use App\Services\OrderService;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;

/** One-step checkout: contact + address + shipping + payment on a single screen. */
class CheckoutController extends Controller
{
    public function show(CartService $cart, PaymentManager $payments)
    {
        $lines = $cart->lines();
        if ($lines->isEmpty()) {
            return redirect(sroute('cart'));
        }

        return view('storefront.checkout', [
            'lines' => $lines,
            'subtotal' => $cart->subtotal(),
            'methods' => $payments->available(),
            'zones' => ShippingZone::where('is_active', true)->get(),
            'branches' => Branch::where('allows_pickup', true)->get(),
            'states' => config('platform.states'),
        ]);
    }

    public function quote(Request $request, CartService $cart, OrderService $orders)
    {
        $q = $orders->quote($cart->lines(), $request->all());
        $this->trackAbandoned($request, $cart);

        return response()->json(collect($q)->except('zone', 'coupon')->all() + ['eta' => $q['zone']?->eta]);
    }

    public function place(Request $request, CartService $cart, OrderService $orders)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'phone' => ['required', 'string', 'regex:/^(\+?249|0)?[19]\d{8}$/'],
            'fulfillment' => 'required|in:delivery,pickup',
            'state' => 'required_if:fulfillment,delivery|nullable|string|max:60',
            'city' => 'nullable|string|max:60',
            'area' => 'nullable|string|max:80',
            'address' => 'required_if:fulfillment,delivery|nullable|string|max:200',
            'landmark' => 'nullable|string|max:120',
            'branch_id' => 'required_if:fulfillment,pickup|nullable|integer',
            'payment_method' => 'required|string',
            'coupon' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:500',
        ], ['phone.regex' => 'رقم الهاتف غير صحيح (مثال: 0912345678)'], [
            'name' => 'الاسم', 'phone' => 'رقم الهاتف', 'state' => 'الولاية', 'address' => 'العنوان', 'payment_method' => 'طريقة الدفع', 'branch_id' => 'الفرع',
        ]);

        $min = (float) current_store()->setting('min_order', 0);
        if ($min && $cart->subtotal() < $min) {
            return back()->withInput()->with('error', 'الحد الأدنى للطلب '.money($min));
        }

        try {
            [$order, $redirect] = $orders->place($cart->lines(), $data);
        } catch (CheckoutException $e) {
            return back()->withInput()->withErrors($e->errors);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $cart->clear();
        AbandonedCart::where('phone', $order->ship_phone)->delete();

        return $redirect ? redirect()->away($redirect) : redirect(sroute('track', ['token' => $order->tracking_token]))->with('placed', true);
    }

    protected function trackAbandoned(Request $request, CartService $cart): void
    {
        $phone = preg_replace('/\D/', '', (string) $request->input('phone'));
        if (strlen($phone) >= 9) {
            AbandonedCart::updateOrCreate(['phone' => WhatsApp::normalize($phone)], [
                'items' => $cart->raw(), 'total' => $cart->subtotal(),
            ]);
        }
    }
}
