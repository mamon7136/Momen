@extends('layouts.dashboard')
@section('title', 'التقارير')
@section('content')
<form class="row mb"><label>من</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" style="width:auto"><label>إلى</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" style="width:auto"><button class="btn ghost">عرض</button></form>
<div class="grid g3">
    @foreach ([['المبيعات', money($summary['sales'])],['عدد الطلبات', $summary['orders']],['متوسط الطلب', money($summary['avg'])],['المحصّل فعلياً', money($summary['paid'])],['تكلفة البضاعة', money($summary['cogs'])],['صافي الربح التقديري', money($summary['profit'])],['الخصومات', money($summary['discounts'])],['رسوم التوصيل', money($summary['shipping'])],['عمولة المنصة', money($summary['commission'])]] as [$l,$v])
        <div class="card"><div class="stat-label">{{ $l }}</div><div class="stat" style="font-size:1.25rem">{{ $v }}</div></div>
    @endforeach
</div>
<div class="card mt"><h3>المبيعات اليومية</h3>
    @php($max = max(1, $daily->max('t')))
    <div style="display:flex;align-items:flex-end;gap:3px;height:160px;direction:ltr">
        @foreach ($daily as $d)<div title="{{ $d->d }}: {{ money($d->t) }} ({{ $d->c }})" style="flex:1;background:var(--p);border-radius:4px 4px 0 0;height:{{ max(2, $d->t / $max * 100) }}%"></div>@endforeach
    </div>
</div>
<div class="grid g3 mt">
    <div class="card"><h3>الأكثر مبيعاً</h3>@foreach ($top as $t)<div class="sum-row"><span>{{ $t->name }}</span><span>{{ $t->qty }} — {{ money($t->revenue) }}</span></div>@endforeach</div>
    <div class="card"><h3>حسب الولاية</h3>@foreach ($byState as $s)<div class="sum-row"><span>{{ $s->ship_state ?? 'استلام' }}</span><span>{{ $s->c }} — {{ money($s->t) }}</span></div>@endforeach</div>
    <div class="card"><h3>حسب طريقة الدفع</h3>@foreach ($byPayment as $p)<div class="sum-row"><span>{{ \App\Services\Payments\PaymentManager::availableDrivers()[$p->payment_method] ?? $p->payment_method }}</span><span>{{ $p->c }} — {{ money($p->t) }}</span></div>@endforeach</div>
</div>
@endsection
