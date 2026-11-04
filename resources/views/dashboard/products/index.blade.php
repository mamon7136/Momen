@extends('layouts.dashboard')
@section('title', 'المنتجات')
@section('content')
<div class="crumbs">{{ icon('home', 14) }} <a href="{{ route('dashboard.home') }}">الرئيسية</a> / المنتجات</div>
<div class="page-head">
    <a href="{{ route('dashboard.products.create') }}" class="btn mint">{{ icon('plus', 18) }} إضافة منتج جديد</a>
    <form class="dt-search" style="max-width:360px">
        <input name="q" value="{{ request('q') }}" placeholder="ابحث باسم المنتج أو SKU"><button>{{ icon('search', 18) }}</button>
    </form>
</div>
@if ($uncategorized)
    <div class="banner amber">{{ icon('alert', 22) }}<div class="grow">لديك {{ $uncategorized }} منتج بدون تصنيف. أضفها لتصنيفات لتسهيل وصول العملاء إليها.</div><a href="{{ route('dashboard.categories.index') }}" class="btn sm ghost">التصنيفات</a></div>
@endif
@forelse ($products as $p)
    <form method="post" action="{{ route('dashboard.products.quick', $p) }}" class="prow">@csrf
        <input type="checkbox" class="chk" aria-label="تحديد">
        <a href="{{ route('dashboard.products.edit', $p) }}" class="pimg">@if ($p->image())<img src="{{ $p->image() }}" alt="" loading="lazy">@else{{ icon('image', 26) }}@endif</a>
        <div class="fields">
            <label class="field name"><span class="fi">{{ icon('package', 16) }}</span><input name="name" value="{{ $p->name }}" required></label>
            <label class="field"><span class="fi">{{ icon('coins', 16) }}</span><input name="price" type="number" step="0.01" value="{{ (float) $p->price }}"><span class="suf">{{ $p->price_currency === 'SDG' ? 'ج.س' : $p->price_currency }}</span></label>
            @if ($p->type === 'simple')
                <label class="field"><span class="fi">{{ icon('orders', 16) }}</span><input name="stock" type="number" value="{{ $p->track_stock ? $p->availableStock() : '' }}" placeholder="∞"><span class="suf">الكمية</span></label>
            @else
                <span class="field"><span class="fi">{{ icon('orders', 16) }}</span><span style="padding:0 6px">{{ $p->type === 'digital' ? 'منتج رقمي' : 'الكمية: '.$p->availableStock().' (خيارات)' }}</span></span>
            @endif
        </div>
        <div class="pside">
            <div class="row" style="gap:6px">
                @if ($p->category)<span class="chip-s">{{ icon('folder', 13) }} {{ $p->category->name }}</span>@endif
                <span class="chip-s" style="{{ $p->is_active ? '' : 'background:var(--d-red-bg);color:var(--d-red)' }}">{{ $p->is_active ? 'منشور' : 'مخفي' }}</span>
                @if ($p->isLowStock())<span class="chip-s" style="background:var(--d-amber-bg);color:var(--d-amber)">مخزون منخفض</span>@endif
                <span class="small muted">مبيعات: {{ $p->sales_count }}</span>
            </div>
            <div class="row" style="gap:6px">
                <a href="{{ route('dashboard.products.edit', $p) }}" class="btn ghost sm">{{ icon('edit', 15) }} بيانات المنتج</a>
                <a href="{{ sroute_for($store, 'product', ['slug' => $p->slug]) }}" target="_blank" class="btn ghost sm">{{ icon('eye', 15) }}</a>
                <button class="btn mint sm" style="flex:1">حفظ</button>
            </div>
        </div>
    </form>
@empty
    <div class="panel empty">{{ icon('shirt', 48) }}<h3>لا توجد منتجات بعد</h3><a href="{{ route('dashboard.products.create') }}" class="btn dark">{{ icon('plus', 16) }} أضف أول منتج</a></div>
@endforelse
{{ $products->links('partials.pagination') }}
@endsection
