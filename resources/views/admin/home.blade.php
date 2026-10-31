@extends('layouts.dashboard')
@section('title', 'لوحة المنصة')
@section('content')
<div class="grid g3">
@foreach ([['المتاجر', $stats['stores'].' ('.$stats['active'].' نشط)'],['إيرادات الاشتراكات الشهرية (MRR)', money($stats['mrr'])],['حجم المبيعات هذا الشهر (GMV)', money($stats['gmv_month'])],['العمولات هذا الشهر', money($stats['commission_month'])],['اشتراكات بانتظار التفعيل', $stats['pending_subs']],['طلبات سحب', $stats['payouts']]] as [$l,$v])
    <div class="card"><div class="stat-label">{{ $l }}</div><div class="stat" style="font-size:1.3rem">{{ $v }}</div></div>
@endforeach
</div>
<div class="table-wrap mt"><table><tr><th>المتجر</th><th>المالك</th><th>الباقة</th><th>الحالة</th><th>التسجيل</th></tr>
@foreach ($latestStores as $s)<tr><td><a href="{{ $s->primaryUrl() }}" target="_blank">{{ $s->name }}</a></td><td>{{ $s->owner->name }} {{ $s->owner->phone }}</td><td>{{ $s->plan?->name }}</td><td><span class="badge b-{{ $s->status }}">{{ $s->status }}</span></td><td class="small">{{ $s->created_at->format('Y-m-d') }}</td></tr>@endforeach
</table></div>
@endsection
