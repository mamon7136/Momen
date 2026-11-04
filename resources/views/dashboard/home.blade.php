@extends('layouts.dashboard')
@section('title', 'الرئيسية')
@section('content')
@if ($receipts->isNotEmpty())
    <div class="banner red">{{ icon('alert', 26) }}<div class="grow"><strong>لديك {{ $receipts->count() }} إيصال دفع بانتظار المراجعة</strong>راجع الإيصالات وأكّدها حتى يبدأ تجهيز الطلبات ويصل التأكيد للعملاء.</div><a href="{{ route('dashboard.orders.index', ['status' => 'payment_review']) }}" class="btn sm danger">مراجعة {{ icon('chev', 14) }}</a></div>
@endif
@if (collect($checklist)->contains('done', false))
    <div class="panel mb">
        <div class="panel-h"><h3>{{ icon('sparkles') }} خطوات إطلاق متجرك</h3><span class="small muted">{{ collect($checklist)->where('done', true)->count() }} / {{ count($checklist) }}</span></div>
        <div class="grid g2">
            @foreach ($checklist as $c)
                <a href="{{ $c['url'] }}" class="row-i" style="border:1px solid var(--d-border);border-radius:12px">
                    <span class="kpi"><span class="ci" style="width:40px;height:40px">{{ $c['done'] ? icon('check', 18) : icon($c['icon'], 18) }}</span></span>
                    <span class="main t" style="{{ $c['done'] ? 'text-decoration:line-through;opacity:.6' : '' }}">{{ $c['label'] }}</span>{{ icon('chev', 16) }}
                </a>
            @endforeach
        </div>
    </div>
@endif

<div class="summary mb">
    <div class="left">
        <div class="row between"><h3 style="margin:0">ملخص الشهر</h3><span class="muted small">{{ now()->translatedFormat('F Y') }}</span></div>
        <div class="small muted">آخر تحديث: الآن</div>
        <div class="kpis">
            <div class="kpi"><span class="ci">{{ icon('users', 22) }}</span><div><div class="v">{{ number_format($kpi['visits']) }}</div><div class="l">الزيارات</div></div></div>
            <div class="kpi"><span class="ci">{{ icon('coins', 22) }}</span><div><div class="v">{{ number_format($kpi['sales']) }}</div><div class="l">المبيعات (ج.س)</div></div></div>
            <div class="kpi"><span class="ci">{{ icon('orders', 22) }}</span><div><div class="v">{{ number_format($kpi['orders']) }}</div><div class="l">الطلبات</div></div></div>
            <div class="kpi">
                <span class="ring" style="--p:{{ $kpi['goal_pct'] }}"><span>{{ icon('target', 18) }}</span></span>
                <div>
                    @if ($kpi['goal'] > 0)<div class="v">{{ number_format($kpi['goal']) }}</div><div class="l">هدف الشهر · {{ $kpi['goal_pct'] }}%</div>
                    @else
                        <form method="post" action="{{ route('dashboard.goal') }}" class="row" style="gap:6px">@csrf<input name="goal" type="number" placeholder="هدف الشهر" style="min-height:34px;width:120px"><button class="btn sm mint">حفظ</button></form>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('dashboard.reports.index') }}" class="small" style="font-weight:700">المزيد من التقارير {{ icon('chev', 14) }}</a>
    </div>
    <div class="chart">
        @php($max = max(1, $days->max('total')))
        <div class="bars">@foreach ($days as $d)<div style="height:{{ max(1, $d['total'] / $max * 100) }}%" data-t="{{ $d['day'] }}: {{ money($d['total']) }}"></div>@endforeach</div>
        <div class="bar-x">@foreach ($days->filter(fn ($d) => $d['day'] % 5 === 1) as $d)<span>{{ $d['day'] }}/{{ now()->month }}</span>@endforeach</div>
    </div>
</div>

<div class="grid" style="grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);align-items:start">
    <div class="rows">
        <div class="rh"><h3>{{ icon('orders') }} أحدث الطلبات</h3><a href="{{ route('dashboard.orders.index') }}" class="small">كل الطلبات {{ icon('chev', 14) }}</a></div>
        @forelse ($latest as $o)@include('partials.order-row')
        @empty<div class="empty">{{ icon('orders', 42) }}<div>لا توجد طلبات بعد — شارك رابط متجرك لتبدأ البيع</div></div>@endforelse
    </div>
    <div class="grid" style="gap:16px">
        <div class="rows">
            <div class="rh"><h3>{{ icon('bell') }} التنبيهات</h3></div>
            <div class="scroll-list">
                @foreach ($receipts as $p)
                    <a href="{{ route('dashboard.orders.show', $p->order_id) }}" class="row-i"><span class="avatar" style="background:#fde2e2;color:#b42318">{{ icon('receipt', 18) }}</span><span class="main"><span class="t">إيصال {{ money($p->amount) }} للطلب #{{ $p->order?->number }}</span><span class="meta"><span>{{ $p->created_at->diffForHumans() }}</span></span></span></a>
                @endforeach
                @forelse ($activity as $h)
                    @if ($h->order)
                        <a href="{{ route('dashboard.orders.show', $h->order_id) }}" class="row-i"><span class="avatar">{{ icon('package', 18) }}</span><span class="main"><span class="t">الطلب #{{ $h->order->number }} — {{ \App\Models\Order::STATUSES[$h->status] ?? $h->status }}</span><span class="meta"><span>{{ $h->note }}</span><span>{{ $h->created_at->diffForHumans() }}</span></span></span></a>
                    @endif
                @empty<div class="empty">لا توجد تنبيهات</div>@endforelse
            </div>
        </div>
        <div class="rows">
            <div class="rh"><h3>{{ icon('alert') }} منتجات نفدت أو قاربت</h3></div>
            <div class="scroll-list" style="max-height:300px">
                @forelse ($outOfStock->concat($lowStock) as $p)
                    <a href="{{ route('dashboard.products.edit', $p) }}" class="row-i"><span class="thumb">@if ($p->image())<img src="{{ $p->image() }}" alt="">@else{{ icon('image', 18) }}@endif</span>
                        <span class="main"><span class="t" style="color:{{ $p->availableStock() <= 0 ? 'var(--d-red)' : 'inherit' }}">{{ $p->name }}</span><span class="meta"><span>{{ money($p->priceSdg()) }}</span><span>المتبقي: {{ $p->availableStock() }}</span></span></span></a>
                @empty<div class="empty">كل المنتجات متوفرة 👍</div>@endforelse
            </div>
        </div>
    </div>
</div>
<style>@media(max-width:1000px){.dc>.grid[style*="1.6fr"]{grid-template-columns:1fr!important}}</style>
@endsection
