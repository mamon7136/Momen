<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>بوليصة {{ $order->shipment?->waybill_number }}</title><link rel="stylesheet" href="{{ asset('css/app.css') }}">
<style>body{background:#fff}.wb{border:2px solid #000;max-width:420px;margin:10px auto;padding:14px;font-size:14px}.wb h2{margin:0}.big{font-size:22px;font-weight:800;letter-spacing:1px;direction:ltr;text-align:center;border:2px dashed #000;padding:8px;margin:8px 0}.r{display:flex;justify-content:space-between;border-bottom:1px solid #ccc;padding:5px 0}</style></head>
<body onload="window.print()">
@php($s = $order->shipment)
<div class="wb">
    <div class="r"><h2>{{ $store->name }}</h2><span>{{ $s?->company?->name ?? 'توصيل المتجر' }}</span></div>
    <div class="big">{{ $s?->waybill_number }}</div>
    <div class="r"><span>رقم الطلب</span><strong>#{{ $order->number }}</strong></div>
    <div class="r"><span>المستلم</span><strong>{{ $order->ship_name }}</strong></div>
    <div class="r"><span>الهاتف</span><strong style="direction:ltr">{{ $order->ship_phone }}</strong></div>
    <div class="r"><span>العنوان</span><strong>{{ $order->ship_state }} - {{ $order->ship_city }} - {{ $order->ship_area }}</strong></div>
    <div>{{ $order->ship_address }} @if ($order->ship_landmark)<br>علامة: {{ $order->ship_landmark }}@endif</div>
    <div class="r"><span>عدد القطع</span><strong>{{ $order->items->sum('quantity') }}</strong></div>
    <div class="big" style="font-size:20px">التحصيل: {{ money($s?->cod_amount ?? 0) }}</div>
    <div class="r"><span>المندوب</span><span>{{ $s?->driver_name }} {{ $s?->driver_phone }}</span></div>
    <div class="small muted">{{ now()->format('Y-m-d H:i') }} — المرسل: {{ $store->phone }}</div>
</div>
</body></html>
