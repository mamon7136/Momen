@extends('layouts.dashboard')
@section('title', 'المتاجر')
@section('content')
<form class="row mb"><input name="q" value="{{ request('q') }}" placeholder="بحث" style="max-width:260px"><select name="status" style="width:auto"><option value="">كل الحالات</option>@foreach (['active','pending','suspended','expired'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select><button class="btn ghost">بحث</button></form>
<div class="table-wrap"><table><tr><th>المتجر</th><th>المالك</th><th>الباقة</th><th>ينتهي</th><th>المحفظة</th><th>الحالة</th></tr>
@foreach ($stores as $s)<tr><td><a href="{{ $s->primaryUrl() }}" target="_blank">{{ $s->name }}</a><div class="small muted">{{ $s->slug }}</div></td><td>{{ $s->owner->name }}<div class="small">{{ $s->owner->phone }}</div></td><td>{{ $s->plan?->name }}</td><td class="small">{{ $s->subscription_ends_at?->format('Y-m-d') ?? '—' }}</td><td>{{ money($s->wallet?->balance) }}</td>
<td><form method="post" action="{{ route('admin.stores.status', $s) }}" class="row">@csrf<select name="status" style="min-height:34px;width:auto">@foreach (['active','pending','suspended','expired'] as $st)<option @selected($s->status===$st)>{{ $st }}</option>@endforeach</select><button class="btn sm ghost">حفظ</button></form></td></tr>@endforeach
</table></div>{{ $stores->links('partials.pagination') }}
@endsection
