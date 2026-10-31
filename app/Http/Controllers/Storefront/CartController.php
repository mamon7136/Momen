<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(CartService $cart)
    {
        return view('storefront.cart', ['lines' => $cart->lines(), 'subtotal' => $cart->subtotal()]);
    }

    public function add(Request $request, CartService $cart)
    {
        $data = $request->validate(['product_id' => 'required|integer', 'variant_id' => 'nullable|integer', 'qty' => 'nullable|integer|min:1|max:99']);
        $product = Product::where('is_active', true)->findOrFail($data['product_id']);
        $variantId = $data['variant_id'] ?? null;
        if ($product->type === 'variable' && ! $product->variants()->whereKey($variantId)->exists()) {
            return back()->with('error', 'يرجى اختيار المقاس/اللون');
        }
        if ($product->availableStock($variantId) < ($data['qty'] ?? 1)) {
            return back()->with('error', 'الكمية غير متوفرة حالياً');
        }
        $cart->add($product->id, $variantId, $data['qty'] ?? 1);

        if ($request->boolean('buy_now')) {
            return redirect(sroute('checkout'));
        }

        return back()->with('status', 'تمت الإضافة إلى السلة 🛒');
    }

    public function update(Request $request, CartService $cart)
    {
        foreach ((array) $request->input('qty', []) as $line => $qty) {
            $cart->update((string) $line, (int) $qty);
        }

        return redirect(sroute('cart'));
    }
}
