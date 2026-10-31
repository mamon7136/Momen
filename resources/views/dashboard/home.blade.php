@extends('layouts.dashboard')
@section('title', 'الرئيسية')
@section('content')
<div class="card mb row between"><div>رابط متجرك: <a href="{{ $store->primaryUrl() }}" target="_blank" style="direction:ltr;display:inline-block">{{ $store->primaryUrl() }}</a></div>
    <a class="btn wa sm" href="https://wa.me/?text={{ rawurlencode('تسوق من متجري: '.$store->primaryUrl()) }}">مشاركة على واتساب</a></div>
@if (collect($checklist)->contains('done', false))
<div class="card mb"><h3>🚀 خطوات إطلاق متجرك</h3>
    @foreach ($checklist as $c)<div class="row between" style="padding:4px 0"><span>{{ $c['done'] ? '✅' : '⬜' }} {{ $c['label'] }}</span>@unless ($c['done'])<a class="btn sm out" href="{{ $c['url'] }}">ابدأ</a>@endunless</div>@endforeach
</div>
@endif
<div class="grid g3">
    <div class="card"><div class="stat-label">مبيعات اليوم</div><div class="stat">{{ money($stats['today_sales']) }}</div><div class="small muted">{{ $stats['today_orders'] }} طلب</div></div>
    <div class="card"><div class="stat-label">مبيعات الشهر</div><div class="stat">{{ money($stats['month_sales']) }}</div><div class="small muted">{{ $stats['month_orders'] }} طلب</div></div>
    <div class="card"><div class="stat-label">بانتظار الإجراء</div><div class="stat">{{ $stats['pending'] }}</div><a class="small" href="{{ route('dashboard.orders.index', ['status' => 'payment_review']) }}">{{ $stats['receipts'] }} إيصال للمراجعة</a></div>
</div>
<div class="grid g2 mt">
    <div class="card"><h3>أحدث الطلبات</h3>
        <div class="table-wrap"><table><tr><th>#</th><th>العميل</th><th>الإجمالي</th><th>الحالة</th></tr>
        @forelse ($latest as $o)<tr><td><a href="{{ route('dashboard.orders.show', $o) }}">{{ $o->number }}</a></td><td>{{ $o->customer->name }}</td><td>{{ money($o->total) }}</td><td><span class="badge b-{{ $o->status }}">{{ $o->statusLabel() }}</span></td></tr>
        @empty<tr><td colspan="4" class="muted center">لا توجد طلبات بعد</td></tr>@endforelse</table></div>
    </div>
    <div class="card"><h3>⚠️ مخزون منخفض</h3>
        @forelse ($lowStock as $p)<div class="row between"><a href="{{ route('dashboard.products.edit', $p) }}">{{ $p->name }}</a><span class="badge b-pending">{{ $p->availableStock() }}</span></div>
        @empty<p class="muted">كل المنتجات متوفرة 👍</p>@endforelse
    </div>
</div>
@endsection
