@extends('layouts.dashboard')
@section('title', 'التقارير')
@section('content')
<div class="crumbs">{{ icon('home', 14) }} <a href="{{ route('dashboard.home') }}">الرئيسية</a> / التقارير</div>
<div class="page-head">
    <form class="panel row" style="padding:10px 14px;gap:10px">
        {{ icon('clock', 18) }} <b>تاريخ التقرير</b>
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" style="width:auto;min-height:38px">
        <span class="muted">—</span>
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" style="width:auto;min-height:38px">
        <button class="btn mint sm">عرض</button>
    </form>
    <button onclick="print()" class="btn ghost no-print">{{ icon('printer', 16) }} طباعة</button>
</div>

@php($rows = [['sales', 'إجمالي المبيعات', 'coins'], ['orders', 'عدد الطلبات', 'orders'], ['avg', 'متوسط قيمة الطلب', 'chart'], ['paid', 'المحصّل فعلياً', 'check'], ['discounts', 'كوبونات التخفيض', 'tag'], ['shipping', 'رسوم الشحن', 'truck'], ['cogs', 'تكاليف المنتجات', 'package'], ['commission', 'عمولة المنصة', 'receipt'], ['profit', 'صافي الربح التقديري', 'wallet']])
<div class="grid" style="grid-template-columns:minmax(0,1.1fr) minmax(0,1fr);align-items:start">
    <div class="rows">
        <div class="rh"><h3>{{ icon('coins') }} المبيعات</h3><span class="small muted">مقارنة بالفترة السابقة</span></div>
        @foreach ($rows as [$k, $l, $i])
            <div class="row-i">
                <span class="avatar" style="width:36px;height:36px">{{ icon($i, 16) }}</span>
                <span class="main t" style="font-weight:600">{{ $l }}</span>
                <span class="amt" style="{{ in_array($k, ['discounts', 'cogs', 'commission']) ? 'color:var(--d-red)' : '' }}">{{ $k === 'orders' ? number_format($summary[$k]) : money($summary[$k]) }}</span>
                <span class="ago" style="min-width:76px">
                    @if ($change[$k] === null)<span class="muted">—</span>
                    @else<span style="color:{{ $change[$k] >= 0 ? 'var(--d-green)' : 'var(--d-red)' }};font-weight:700;direction:ltr;display:inline-flex;gap:2px">{{ icon($change[$k] >= 0 ? 'up' : 'down', 14) }} {{ abs($change[$k]) }}%</span>@endif
                </span>
            </div>
        @endforeach
    </div>
    <div class="grid" style="gap:16px">
        <div class="panel">
            <div class="panel-h"><h3>{{ icon('chart') }} المبيعات اليومية</h3><span class="small muted">{{ number_format($visits) }} زيارة · تحويل {{ $visits ? round($summary['orders'] / $visits * 100, 1) : 0 }}%</span></div>
            @php($max = max(1, $daily->max('t')))
            <div class="bars" style="height:170px;min-height:0">@forelse ($daily as $d)<div style="height:{{ max(2, $d->t / $max * 100) }}%" data-t="{{ $d->d }}: {{ money($d->t) }} ({{ $d->c }})"></div>@empty<span class="muted small" style="margin:auto">لا توجد مبيعات في هذه الفترة</span>@endforelse</div>
        </div>
        <div class="rows">
            <div class="rh"><h3>{{ icon('star') }} الأكثر مبيعاً</h3></div>
            @forelse ($top as $t)<div class="row-i"><span class="main t">{{ $t->name }}</span><span class="small muted">{{ $t->qty }} قطعة</span><span class="amt">{{ money($t->revenue) }}</span></div>
            @empty<div class="empty">لا توجد بيانات</div>@endforelse
        </div>
    </div>
</div>
<div class="grid g2 mt">
    <div class="rows"><div class="rh"><h3>{{ icon('pin') }} حسب الولاية</h3></div>
        @forelse ($byState as $s)<div class="row-i"><span class="main t">{{ $s->ship_state ?? 'استلام من الفرع' }}</span><span class="small muted">{{ $s->c }} طلب</span><span class="amt">{{ money($s->t) }}</span></div>@empty<div class="empty">—</div>@endforelse
    </div>
    <div class="rows"><div class="rh"><h3>{{ icon('card') }} حسب طريقة الدفع</h3></div>
        @forelse ($byPayment as $p)<div class="row-i"><span class="main t">{{ \App\Services\Payments\PaymentManager::availableDrivers()[$p->payment_method] ?? $p->payment_method }}</span><span class="small muted">{{ $p->c }} طلب</span><span class="amt">{{ money($p->t) }}</span></div>@empty<div class="empty">—</div>@endforelse
    </div>
</div>
<style>@media(max-width:1000px){.dc>.grid[style*="1.1fr"]{grid-template-columns:1fr!important}}</style>
@endsection
