<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>فاتورة #{{ $order->number }}</title><link rel="stylesheet" href="{{ asset('css/app.css') }}"><style>body{background:#fff}</style></head>
<body onload="window.print()"><div class="container" style="max-width:720px;padding:20px">
<div class="row between"><div><h2>{{ $store->name }}</h2><div class="small">{{ $store->phone }} — {{ $store->city }}</div></div><div><h2>فاتورة</h2><div>#{{ $order->number }}</div><div class="small">{{ $order->created_at->format('Y-m-d') }}</div></div></div>
<p class="mt"><strong>العميل:</strong> {{ $order->ship_name }} — {{ $order->ship_phone }}<br>{{ $order->ship_state }} {{ $order->ship_city }} {{ $order->ship_address }}</p>
<table><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr>@foreach ($order->items as $i)<tr><td>{{ $i->name }} {{ $i->options ? '('.collect($i->options)->implode('/').')' : '' }}</td><td>{{ $i->quantity }}</td><td>{{ money($i->unit_price) }}</td><td>{{ money($i->total) }}</td></tr>@endforeach</table>
<div style="max-width:300px;margin-inline-start:auto" class="mt">
<div class="sum-row"><span>المجموع</span><span>{{ money($order->subtotal) }}</span></div><div class="sum-row"><span>الخصم</span><span>{{ money($order->discount) }}</span></div>
<div class="sum-row"><span>التوصيل</span><span>{{ money($order->shipping_fee) }}</span></div><div class="sum-row total"><span>الإجمالي</span><span>{{ money($order->total) }}</span></div>
<div class="small">حالة الدفع: {{ $order->paymentStatusLabel() }}</div></div>
</div></body></html>
