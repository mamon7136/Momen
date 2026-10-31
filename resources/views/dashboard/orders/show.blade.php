@extends('layouts.dashboard')
@section('title', 'طلب #'.$order->number)
@section('content')
<div class="row mb no-print">
    <a class="btn ghost sm" href="{{ route('dashboard.orders.invoice', $order) }}" target="_blank">🧾 الفاتورة</a>
    @if ($order->shipment)<a class="btn ghost sm" href="{{ route('dashboard.orders.waybill', $order) }}" target="_blank">📦 بوليصة الشحن</a>@endif
    <a class="btn wa sm" href="{{ \App\Services\Messaging\WhatsApp::link($order->ship_phone, 'مرحباً '.$order->ship_name.'، بخصوص طلبك رقم '.$order->number.' من '.$store->name) }}" target="_blank">💬 مراسلة العميل</a>
    <a class="btn ghost sm" href="tel:{{ $order->ship_phone }}">📞 اتصال</a>
</div>
<div class="grid g2" style="align-items:start">
<div>
    <div class="card mb">
        <div class="row between"><h3>المنتجات</h3><span class="badge b-{{ $order->status }}">{{ $order->statusLabel() }}</span></div>
        @foreach ($order->items as $i)<div class="sum-row"><span>{{ $i->name }} @if ($i->options)<small class="muted">({{ collect($i->options)->implode(' / ') }})</small>@endif × {{ $i->quantity }}</span><span>{{ money($i->total) }}</span></div>@endforeach
        <div class="sum-row mt"><span>المجموع</span><span>{{ money($order->subtotal) }}</span></div>
        @if ($order->discount > 0)<div class="sum-row"><span>خصم {{ $order->coupon?->code }}</span><span>-{{ money($order->discount) }}</span></div>@endif
        <div class="sum-row"><span>التوصيل ({{ $order->shippingZone?->label() }})</span><span>{{ money($order->shipping_fee) }}</span></div>
        @if ($order->payment_fee > 0)<div class="sum-row"><span>رسوم الدفع</span><span>{{ money($order->payment_fee) }}</span></div>@endif
        <div class="sum-row total"><span>الإجمالي</span><span>{{ money($order->total) }}</span></div>
        @if ($order->cod_deposit > 0)<div class="small muted">عربون مطلوب: {{ money($order->cod_deposit) }}</div>@endif
    </div>
    <div class="card mb">
        <h3>العميل والتوصيل</h3>
        <div>{{ $order->ship_name }} — <span style="direction:ltr;display:inline-block">{{ $order->ship_phone }}</span></div>
        @if ($order->fulfillment === 'pickup')<div>🏬 استلام من: {{ $order->branch?->name }}</div>
        @else<div>📍 {{ $order->ship_state }} / {{ $order->ship_city }} / {{ $order->ship_area }}</div><div>{{ $order->ship_address }}</div>@if ($order->ship_landmark)<div class="muted">علامة: {{ $order->ship_landmark }}</div>@endif @endif
        @if ($order->notes)<div class="alert warn">📝 {{ $order->notes }}</div>@endif
        <div class="small muted mt">طلبات العميل السابقة: {{ $order->customer->orders_count }}</div>
    </div>
    <div class="card"><h3>السجل</h3><ul class="timeline">@foreach ($order->history as $h)<li><strong>{{ \App\Models\Order::STATUSES[$h->status] ?? $h->status }}</strong> <span class="small muted">{{ $h->created_at->format('Y-m-d H:i') }}</span>@if ($h->note)<div class="small">{{ $h->note }}</div>@endif</li>@endforeach</ul></div>
</div>
<div>
    <div class="card mb">
        <h3>💳 الدفع — <span class="badge b-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span></h3>
        <div class="small muted">الطريقة: {{ \App\Services\Payments\PaymentManager::availableDrivers()[$order->payment_method] ?? $order->payment_method }}</div>
        @foreach ($order->payments as $p)
            <div class="card mt">
                <div class="row between"><strong>{{ money($p->amount) }}</strong><span class="badge b-{{ $p->status }}">{{ $p->status }}</span></div>
                <div class="small">رقم العملية: <strong>{{ $p->reference }}</strong> @if ($p->sender_name) — من: {{ $p->sender_name }} @endif</div>
                @foreach ($p->gateway_payload['flags'] ?? [] as $flag)<div class="alert err small">⚠️ {{ $flag === 'duplicate_reference' ? 'رقم العملية استُخدم في طلب آخر — تحقق جيداً!' : 'نفس صورة الإيصال رُفعت من قبل — احتمال احتيال' }}</div>@endforeach
                @if ($p->receipt_path)<a href="{{ route('dashboard.payments.receipt', $p) }}" target="_blank"><img src="{{ route('dashboard.payments.receipt', $p) }}" style="max-height:260px;border-radius:8px;margin:8px 0" alt="receipt"></a>@endif
                @if ($p->status === 'review')
                    <div class="row">
                        <form method="post" action="{{ route('dashboard.payments.approve', $p) }}">@csrf<button class="btn ok sm">✓ تأكيد الاستلام</button></form>
                        <form method="post" action="{{ route('dashboard.payments.reject', $p) }}" class="row">@csrf<input name="reason" placeholder="سبب الرفض" required style="min-height:34px;width:150px"><button class="btn danger sm">رفض</button></form>
                    </div>
                @elseif ($p->review_note)<div class="small muted">{{ $p->review_note }}</div>@endif
            </div>
        @endforeach
    </div>
    <div class="card mb">
        <h3>تحديث الحالة</h3>
        <form method="post" action="{{ route('dashboard.orders.status', $order) }}">@csrf
            <select name="status">@foreach (\App\Models\Order::STATUSES as $k=>$l)<option value="{{ $k }}" @selected($order->status===$k)>{{ $l }}</option>@endforeach</select>
            <label>ملاحظة للعميل (اختياري)</label><input name="note">
            <label class="check"><input type="checkbox" name="notify" value="1" checked> إشعار العميل عبر واتساب</label>
            <button class="btn mt">تحديث</button>
        </form>
    </div>
    @if ($order->fulfillment === 'delivery' && ! $order->shipment && ! in_array($order->status, ['cancelled','delivered']))
    <div class="card">
        <h3>🚚 إصدار بوليصة شحن</h3>
        <form method="post" action="{{ route('dashboard.orders.shipment', $order) }}">@csrf
            <label>شركة الشحن / المندوب</label><select name="shipping_company_id"><option value="">مندوب المتجر</option>@foreach ($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
            <div class="grid g2"><div><label>اسم المندوب</label><input name="driver_name"></div><div><label>هاتف المندوب</label><input name="driver_phone"></div></div>
            <label>رقم التتبع لدى الشركة</label><input name="tracking_number">
            <button class="btn mt">إصدار وطباعة البوليصة</button>
        </form>
    </div>
    @elseif ($order->shipment)
    <div class="card"><h3>🚚 الشحنة {{ $order->shipment->waybill_number }}</h3><div>{{ $order->shipment->company?->name ?? 'مندوب المتجر' }} — {{ $order->shipment->driver_name }} {{ $order->shipment->driver_phone }}</div><div>مبلغ التحصيل: <strong>{{ money($order->shipment->cod_amount) }}</strong></div></div>
    @endif
</div>
</div>
@endsection
