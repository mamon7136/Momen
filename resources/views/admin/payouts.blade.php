@extends('layouts.dashboard')
@section('title', 'طلبات السحب')
@section('content')
<div class="table-wrap"><table><tr><th>المتجر</th><th>المبلغ</th><th>الحساب</th><th>التاريخ</th><th>الحالة</th><th></th></tr>
@foreach ($payouts as $p)<tr><td>{{ $p->store->name }}</td><td>{{ money($p->amount) }}</td><td>{{ $p->method }} — {{ $p->account_details }}</td><td class="small">{{ $p->created_at->format('Y-m-d') }}</td><td>{{ $p->status }}</td>
<td>@if ($p->status === 'requested')<form method="post" action="{{ route('admin.payouts.paid', $p) }}" class="row">@csrf<input name="reference" placeholder="رقم العملية" style="min-height:34px;width:130px"><button class="btn ok sm">تم التحويل</button></form>@endif</td></tr>@endforeach
</table></div>{{ $payouts->links('partials.pagination') }}
@endsection
