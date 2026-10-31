@extends('layouts.dashboard')
@section('title', 'المنتجات')
@section('content')
<div class="row between mb"><form class="row"><input name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو SKU"><button class="btn ghost">بحث</button></form><a href="{{ route('dashboard.products.create') }}" class="btn">+ منتج جديد</a></div>
<div class="table-wrap"><table>
<tr><th></th><th>المنتج</th><th>النوع</th><th>السعر</th><th>المخزون</th><th>المبيعات</th><th>الحالة</th><th></th></tr>
@forelse ($products as $p)
<tr>
    <td style="width:56px">@if ($p->image())<img src="{{ $p->image() }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px" alt="">@endif</td>
    <td><strong>{{ $p->name }}</strong><div class="small muted">{{ $p->category?->name }} {{ $p->sku }}</div></td>
    <td>{{ ['simple'=>'بسيط','variable'=>'متغير','digital'=>'رقمي'][$p->type] }}</td>
    <td>{{ money($p->priceSdg()) }} @if ($p->price_currency !== 'SDG')<div class="small muted">{{ $p->price }} {{ $p->price_currency }}</div>@endif</td>
    <td>@if ($p->track_stock)<span class="badge {{ $p->isLowStock() ? 'b-pending' : 'b-active' }}">{{ $p->availableStock() }}</span>@else ∞ @endif</td>
    <td>{{ $p->sales_count }}</td>
    <td><span class="badge {{ $p->is_active ? 'b-active' : 'b-cancelled' }}">{{ $p->is_active ? 'منشور' : 'مخفي' }}</span></td>
    <td class="row"><a href="{{ route('dashboard.products.edit', $p) }}" class="btn sm ghost">تعديل</a>
        <form method="post" action="{{ route('dashboard.products.destroy', $p) }}" onsubmit="return confirm('حذف المنتج؟')">@csrf @method('delete')<button class="btn sm danger">حذف</button></form></td>
</tr>
@empty<tr><td colspan="8" class="center muted">لا توجد منتجات. <a href="{{ route('dashboard.products.create') }}">أضف أول منتج</a></td></tr>@endforelse
</table></div>
{{ $products->links('partials.pagination') }}
@endsection
