<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingCompany;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('customer')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('number', $s)->orWhere('ship_phone', 'like', "%$s%")->orWhere('ship_name', 'like', "%$s%")))
            ->latest()->paginate(25)->withQueryString();

        return view('dashboard.orders.index', ['orders' => $orders, 'counts' => Order::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status')]);
    }

    public function show(Order $order)
    {
        return view('dashboard.orders.show', [
            'order' => $order->load('items', 'customer', 'payments', 'history', 'shipment.company', 'shippingZone', 'branch', 'coupon'),
            'companies' => ShippingCompany::where('is_active', true)->where(fn ($q) => $q->whereNull('store_id')->orWhere('store_id', current_store()->id))->get(),
        ]);
    }

    public function status(Request $request, Order $order, OrderService $orders)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Order::STATUSES))], 'note' => 'nullable|string|max:300']);
        $orders->updateStatus($order, $data['status'], $data['note'] ?? null, $request->user()->id, $request->boolean('notify', true));

        return back()->with('status', 'تم تحديث حالة الطلب');
    }

    public function shipment(Request $request, Order $order, OrderService $orders)
    {
        $data = $request->validate([
            'shipping_company_id' => 'nullable|integer',
            'tracking_number' => 'nullable|string|max:60',
            'driver_name' => 'nullable|string|max:80',
            'driver_phone' => 'nullable|string|max:20',
        ]);
        $orders->createShipment($order, $data + ['user_id' => $request->user()->id]);

        return redirect()->route('dashboard.orders.waybill', $order);
    }

    public function waybill(Order $order)
    {
        return view('dashboard.orders.waybill', ['order' => $order->load('items', 'shipment.company', 'customer')]);
    }

    public function invoice(Order $order)
    {
        return view('dashboard.orders.invoice', ['order' => $order->load('items', 'customer')]);
    }
}
