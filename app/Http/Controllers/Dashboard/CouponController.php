<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        return view('dashboard.coupons', ['coupons' => Coupon::latest()->get()]);
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);
        $data = $request->validate([
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('coupons')->where('store_id', current_store()->id)],
            'type' => 'required|in:percent,fixed,free_shipping',
            'value' => 'nullable|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_customer_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);
        if ($data['type'] === 'percent' && ($data['value'] ?? 0) > 100) {
            return back()->withErrors(['value' => 'النسبة لا تتجاوز 100%']);
        }
        Coupon::create($data + ['value' => $data['value'] ?? 0]);

        return back()->with('status', 'تم إنشاء الكوبون');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back();
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return back()->with('status', 'تم حذف الكوبون');
    }
}
