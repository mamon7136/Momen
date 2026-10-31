<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ShippingCompany;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index()
    {
        return view('dashboard.shipping', [
            'zones' => ShippingZone::with('company')->orderBy('state')->orderBy('city')->get(),
            'companies' => ShippingCompany::where(fn ($q) => $q->whereNull('store_id')->orWhere('store_id', current_store()->id))->get(),
            'branches' => Branch::all(),
            'states' => config('platform.states'),
        ]);
    }

    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'state' => 'required|string|max:60', 'city' => 'nullable|string|max:60', 'area' => 'nullable|string|max:80',
            'price' => 'required|numeric|min:0', 'free_over' => 'nullable|numeric|min:0', 'price_per_kg' => 'nullable|numeric|min:0',
            'eta' => 'nullable|string|max:40', 'shipping_company_id' => 'nullable|integer',
        ]);
        ShippingZone::create($data + ['cod_allowed' => $request->boolean('cod_allowed'), 'price_per_kg' => $data['price_per_kg'] ?? 0]);

        return back()->with('status', 'تمت إضافة منطقة التوصيل');
    }

    public function destroyZone(ShippingZone $zone)
    {
        $zone->delete();

        return back();
    }

    public function storeCompany(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'type' => 'required|in:courier,motorcycle,rickshaw,bus_cargo', 'tracking_url' => 'nullable|url']);
        ShippingCompany::create($data + ['store_id' => current_store()->id]);

        return back()->with('status', 'تمت إضافة شركة/مندوب التوصيل');
    }

    public function storeBranch(Request $request)
    {
        $max = current_store()->plan?->max_branches;
        if ($max && Branch::count() >= $max) {
            return back()->with('error', 'وصلت للحد الأقصى من الفروع في باقتك');
        }
        $data = $request->validate(['name' => 'required|string|max:80', 'state' => 'nullable|string', 'city' => 'nullable|string', 'address' => 'nullable|string|max:200', 'phone' => 'nullable|string|max:20']);
        Branch::create($data + ['allows_pickup' => $request->boolean('allows_pickup')]);

        return back()->with('status', 'تمت إضافة الفرع');
    }
}
