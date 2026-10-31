@extends('layouts.dashboard')
@section('title', 'العملاء')
@section('content')
<form class="row mb"><input name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو الهاتف" style="max-width:300px"><button class="btn ghost">بحث</button></form>
<div class="table-wrap"><table><tr><th>العميل</th><th>الهاتف</th><th>الطلبات</th><th>إجمالي المشتريات</th><th></th></tr>
@foreach ($customers as $c)<tr><td>{{ $c->name }}</td><td style="direction:ltr;text-align:right">{{ $c->phone }}</td><td>{{ $c->orders_count }}</td><td>{{ money($c->total_spent) }}</td><td><a class="btn wa sm" href="{{ \App\Services\Messaging\WhatsApp::link($c->phone) }}" target="_blank">واتساب</a></td></tr>@endforeach
</table></div>{{ $customers->links('partials.pagination') }}
@endsection
