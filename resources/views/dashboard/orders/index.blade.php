@extends('layouts.dashboard')
@section('title', 'الطلبات')
@section('content')
<div class="crumbs">{{ icon('home', 14) }} <a href="{{ route('dashboard.home') }}">الرئيسية</a> / الطلبات</div>
<div class="page-head">
    <h1>الطلبات</h1>
    <form class="row" style="gap:8px">
        <select name="payment" onchange="this.form.submit()" style="width:auto;min-height:40px"><option value="">كل حالات الدفع</option>@foreach (\App\Models\Order::PAYMENT_STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('payment')===$k)>{{ $l }}</option>@endforeach</select>
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    </form>
</div>
@php($icons = ['pending'=>'clock','awaiting_payment'=>'coins','payment_review'=>'receipt','confirmed'=>'check','processing'=>'package','ready'=>'store','shipped'=>'truck','delivered'=>'check','cancelled'=>'x-circle','returned'=>'orders'])
<div class="stat-cards">
    <a href="{{ route('dashboard.orders.index') }}" class="scard {{ request('status') ? '' : 'on' }}"><span class="top">{{ icon('orders') }}</span><span class="bot"><span>كل الطلبات</span><b>{{ $counts->sum() }}</b></span></a>
    @foreach (\App\Models\Order::STATUSES as $k => $l)
        <a href="{{ route('dashboard.orders.index', ['status' => $k]) }}" class="scard {{ request('status') === $k ? 'on' : '' }}"><span class="top">{{ icon($icons[$k] ?? 'orders') }}</span><span class="bot"><span><i class="sdot s-{{ $k }}"></i>{{ $l }}</span><b>{{ $counts[$k] ?? 0 }}</b></span></a>
    @endforeach
</div>
<div class="rows">
    <div class="rh"><h3>{{ icon('orders') }} {{ request('status') ? \App\Models\Order::STATUSES[request('status')] ?? 'الطلبات' : 'الطلبات' }} @if (request('q'))<span class="chip-s">بحث: {{ request('q') }}</span>@endif</h3><span class="small muted">{{ $orders->total() }} طلب</span></div>
    @forelse ($orders as $o)@include('partials.order-row')
    @empty<div class="empty">{{ icon('orders', 42) }}<div>لا توجد طلبات</div></div>@endforelse
</div>
{{ $orders->links('partials.pagination') }}
@endsection
