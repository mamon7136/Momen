@extends('layouts.store')
@section('title', 'طلب #'.$order->number)
@section('content')
@if (session('placed'))<div class="alert ok">🎉 تم استلام طلبك بنجاح! رقم الطلب #{{ $order->number }} — احفظ هذا الرابط لتتبع طلبك.</div>@endif
<div class="grid g2" style="align-items:start">
    <div class="card">
        <div class="row between"><h2>طلب #{{ $order->number }}</h2><span class="badge b-{{ $order->status }}">{{ $order->statusLabel() }}</span></div>
        <p class="muted small">{{ $order->created_at->format('Y-m-d H:i') }} — الدفع: <span class="badge b-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span></p>
        @foreach ($order->items as $i)<div class="sum-row"><span>{{ $i->name }} @if ($i->options)<small class="muted">({{ collect($i->options)->implode(' / ') }})</small>@endif × {{ $i->quantity }}</span><span>{{ money($i->total) }}</span></div>@endforeach
        <div class="sum-row mt"><span>المجموع</span><span>{{ money($order->subtotal) }}</span></div>
        @if ($order->discount > 0)<div class="sum-row"><span>الخصم</span><span>-{{ money($order->discount) }}</span></div>@endif
        <div class="sum-row"><span>التوصيل</span><span>{{ money($order->shipping_fee) }}</span></div>
        <div class="sum-row total"><span>الإجمالي</span><span>{{ money($order->total) }}</span></div>
        @if ($order->fulfillment === 'pickup' && $order->branch)<p class="mt">🏬 الاستلام من: <strong>{{ $order->branch->name }}</strong> — {{ $order->branch->address }}</p>
        @else<p class="mt small muted">📍 {{ $order->ship_state }} - {{ $order->ship_city }} - {{ $order->ship_area }} — {{ $order->ship_address }}</p>@endif
        @if ($order->shipment)
            <div class="card mt"><strong>🚚 الشحنة: {{ $order->shipment->waybill_number }}</strong>
                @if ($order->shipment->driver_phone)<div>المندوب: {{ $order->shipment->driver_name }} — <a href="tel:{{ $order->shipment->driver_phone }}">{{ $order->shipment->driver_phone }}</a></div>@endif
                @if ($link = $order->shipment->company?->trackingLink($order->shipment->tracking_number))<a href="{{ $link }}" target="_blank">تتبع لدى شركة الشحن</a>@endif
            </div>
        @endif
    </div>

    <div>
        @php($needsDeposit = $order->payment_method === 'cod' && $order->cod_deposit > 0 && ! $order->payments->where('status', 'approved')->count())
        @if ($order->payment_status !== 'paid' && (($driver && $driver->requiresProof()) || $needsDeposit))
            @php($payDriver = $needsDeposit ? app(\App\Services\Payments\PaymentManager::class)->forKey('mbok') ?? app(\App\Services\Payments\PaymentManager::class)->forKey('bank_transfer') : $driver)
            @php($pending = $order->payments->firstWhere('status', 'review'))
            <div class="card mb">
                @if ($pending)
                    <div class="alert ok">⏳ تم استلام إيصالك (رقم العملية {{ $pending->reference }}) وهو قيد المراجعة.</div>
                @elseif ($payDriver)
                    <h3>💳 أكمل الدفع عبر {{ $payDriver->label() }}</h3>
                    <div class="alert warn">
                        المبلغ المطلوب: <strong>{{ money($needsDeposit ? $order->cod_deposit : $order->total) }}</strong>@if ($needsDeposit) (عربون)@endif<br>
                        رقم الحساب: <strong style="direction:ltr;display:inline-block">{{ $payDriver->config('account_number') }}</strong> <button type="button" class="btn ghost sm" onclick="navigator.clipboard.writeText('{{ $payDriver->config('account_number') }}')">نسخ</button><br>
                        اسم الحساب: <strong>{{ $payDriver->config('account_name') }}</strong><br>
                        اكتب في التعليق: <strong>طلب {{ $order->number }}</strong>
                    </div>
                    @if ($payDriver->instructions())<p class="small">{!! nl2br(e($payDriver->instructions())) !!}</p>@endif
                    <form method="post" action="{{ sroute('track.receipt', ['token' => $order->tracking_token]) }}" enctype="multipart/form-data">@csrf
                        <input type="hidden" name="via" value="{{ $payDriver->key() }}">
                        <label>رقم العملية (Transaction ID)</label><input name="reference" required inputmode="numeric" value="{{ old('reference') }}">
                        <label>اسم المحوِّل (اختياري)</label><input name="sender_name" value="{{ old('sender_name') }}">
                        <label>صورة الإشعار (Screenshot)</label><input type="file" name="receipt" accept="image/*" required>
                        <button class="btn w100 mt">إرسال الإيصال</button>
                    </form>
                @endif
            </div>
        @endif
        <div class="card">
            <h3>مراحل الطلب</h3>
            <ul class="timeline">@foreach ($order->history->reverse() as $h)<li><strong>{{ \App\Models\Order::STATUSES[$h->status] ?? $h->status }}</strong><div class="small muted">{{ $h->created_at->format('Y-m-d H:i') }} @if ($h->note) — {{ $h->note }} @endif</div></li>@endforeach</ul>
        </div>
        @if ($store->whatsapp)<a class="btn wa w100 mt" href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp, 'استفسار عن الطلب رقم '.$order->number) }}">💬 تواصل مع المتجر بخصوص الطلب</a>@endif
    </div>
</div>
@endsection
