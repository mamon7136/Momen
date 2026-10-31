@extends('layouts.dashboard')
@section('title', 'الطلبات')
@section('content')
<div class="chips mb">
    <a class="chip" href="{{ route('dashboard.orders.index') }}">الكل</a>
    @foreach (\App\Models\Order::STATUSES as $k => $l)<a class="chip" href="{{ route('dashboard.orders.index', ['status' => $k]) }}" @if (request('status')===$k) style="border-color:var(--p);color:var(--p)" @endif>{{ $l }} ({{ $counts[$k] ?? 0 }})</a>@endforeach
</div>
<form class="row mb"><input name="q" value="{{ request('q') }}" placeholder="رقم الطلب / الهاتف / الاسم" style="max-width:300px"><button class="btn ghost">بحث</button></form>
<div class="table-wrap"><table>
<tr><th>#</th><th>التاريخ</th><th>العميل</th><th>المنطقة</th><th>الإجمالي</th><th>الدفع</th><th>الحالة</th></tr>
@forelse ($orders as $o)
<tr onclick="location='{{ route('dashboard.orders.show', $o) }}'" style="cursor:pointer">
    <td><a href="{{ route('dashboard.orders.show', $o) }}">{{ $o->number }}</a></td><td class="small">{{ $o->created_at->format('m-d H:i') }}</td>
    <td>{{ $o->ship_name }}<div class="small muted" style="direction:ltr;text-align:right">{{ $o->ship_phone }}</div></td>
    <td class="small">{{ $o->fulfillment === 'pickup' ? 'استلام من الفرع' : $o->ship_city.' - '.$o->ship_area }}</td>
    <td>{{ money($o->total) }}</td>
    <td><span class="badge b-{{ $o->payment_status }}">{{ $o->paymentStatusLabel() }}</span><div class="small muted">{{ $o->payment_method }}</div></td>
    <td><span class="badge b-{{ $o->status }}">{{ $o->statusLabel() }}</span></td>
</tr>
@empty<tr><td colspan="7" class="center muted">لا توجد طلبات</td></tr>@endforelse
</table></div>
{{ $orders->links('partials.pagination') }}
@endsection
