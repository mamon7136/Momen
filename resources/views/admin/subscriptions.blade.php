@extends('layouts.dashboard')
@section('title', 'الاشتراكات')
@section('content')
<div class="table-wrap"><table><tr><th>المتجر</th><th>الباقة</th><th>المبلغ</th><th>الدفع</th><th>الحالة</th><th></th></tr>
@foreach ($subscriptions as $s)<tr><td>{{ $s->store->name }}</td><td>{{ $s->plan->name }} ({{ $s->cycle }})</td><td>{{ money($s->amount) }}</td>
<td class="small">{{ $s->payment_method }} {{ $s->payment_reference }} @if ($s->receipt_path)<a href="{{ route('admin.subscriptions.receipt', $s) }}" target="_blank">الإيصال</a>@endif</td>
<td><span class="badge b-{{ $s->status }}">{{ $s->status }}</span></td>
<td>@if ($s->status === 'pending')<form method="post" action="{{ route('admin.subscriptions.approve', $s) }}">@csrf<button class="btn ok sm">تفعيل</button></form>@endif</td></tr>@endforeach
</table></div>{{ $subscriptions->links('partials.pagination') }}
@endsection
