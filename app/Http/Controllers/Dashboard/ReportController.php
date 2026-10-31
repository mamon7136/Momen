<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from') ?? now()->subDays(29)->startOfDay();
        $to = ($request->date('to') ?? now())->endOfDay();
        $valid = fn ($q) => $q->whereBetween('orders.created_at', [$from, $to])->whereNotIn('orders.status', ['cancelled', 'returned']);

        $orders = Order::query()->tap($valid);
        $summary = [
            'orders' => (clone $orders)->count(),
            'sales' => (float) (clone $orders)->sum('total'),
            'shipping' => (float) (clone $orders)->sum('shipping_fee'),
            'discounts' => (float) (clone $orders)->sum('discount'),
            'commission' => (float) (clone $orders)->sum('commission'),
            'paid' => (float) (clone $orders)->where('payment_status', 'paid')->sum('total'),
        ];
        $summary['avg'] = $summary['orders'] ? $summary['sales'] / $summary['orders'] : 0;

        $items = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')->where('order_items.store_id', current_store()->id)->tap($valid);
        $summary['cogs'] = (float) (clone $items)->sum(DB::raw('COALESCE(order_items.unit_cost, 0) * order_items.quantity'));
        $summary['profit'] = $summary['sales'] - $summary['shipping'] - $summary['cogs'] - $summary['commission'];

        $daily = Order::query()->tap($valid)->selectRaw('DATE(created_at) d, COUNT(*) c, SUM(total) t')->groupBy('d')->orderBy('d')->get();
        $top = (clone $items)->selectRaw('order_items.name, SUM(order_items.quantity) qty, SUM(order_items.total) revenue')
            ->groupBy('order_items.name')->orderByDesc('qty')->limit(10)->get();
        $byState = Order::query()->tap($valid)->selectRaw('ship_state, COUNT(*) c, SUM(total) t')->groupBy('ship_state')->orderByDesc('c')->get();
        $byPayment = Order::query()->tap($valid)->selectRaw('payment_method, COUNT(*) c, SUM(total) t')->groupBy('payment_method')->get();

        return view('dashboard.reports', compact('summary', 'daily', 'top', 'byState', 'byPayment', 'from', 'to'));
    }
}
