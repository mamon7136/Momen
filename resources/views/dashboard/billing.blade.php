@extends('layouts.dashboard')
@section('title', 'الاشتراك والفوترة')
@section('content')
<div class="card mb row between"><div>الباقة الحالية: <strong>{{ $store->plan?->name }}</strong> — الحالة: <span class="badge b-{{ $store->status }}">{{ $store->status }}</span>
    @if ($store->subscription_ends_at)<div class="small muted">ينتهي في {{ $store->subscription_ends_at->format('Y-m-d') }}</div>@endif</div></div>
<div class="grid g2" style="align-items:start">
<form method="post" action="{{ route('dashboard.billing.renew') }}" enctype="multipart/form-data" class="card">@csrf
    <h3>{{ $pending ? 'إكمال دفع الاشتراك' : 'تجديد / ترقية الاشتراك' }}</h3>
    <label>الباقة</label><select name="plan_id">@foreach ($plans->reject->isFree() as $p)<option value="{{ $p->id }}" @selected(($pending->plan_id ?? $store->plan_id) == $p->id)>{{ $p->name }} — {{ money($p->price_monthly) }}/شهر — {{ money($p->price_yearly) }}/سنة</option>@endforeach</select>
    <label>المدة</label><select name="cycle"><option value="monthly">شهري</option><option value="yearly" @selected(($pending->cycle ?? '') === 'yearly')>سنوي</option></select>
    <div class="alert warn mt">حوّل المبلغ إلى:<br>{{ $billing['bank_name'] }} — <strong style="direction:ltr;display:inline-block">{{ $billing['account_number'] }}</strong><br>باسم: {{ $billing['account_name'] }}<br>واكتب في التعليق: <strong>{{ $store->slug }}</strong></div>
    <label>طريقة الدفع</label><select name="method"><option value="mbok">بنكك</option><option value="bank_transfer">تحويل بنكي</option></select>
    <label>رقم العملية</label><input name="reference" required><label>صورة الإشعار</label><input type="file" name="receipt" accept="image/*" required>
    <button class="btn mt w100">إرسال الإيصال</button>
    @if ($pending?->receipt_path)<p class="alert ok small">تم استلام إيصال سابق وهو قيد المراجعة.</p>@endif
</form>
<div class="table-wrap"><table><tr><th>الباقة</th><th>المبلغ</th><th>الفترة</th><th>الحالة</th></tr>
@foreach ($subscriptions as $s)<tr><td>{{ $s->plan->name }} ({{ $s->cycle === 'yearly' ? 'سنوي' : 'شهري' }})</td><td>{{ money($s->amount) }}</td><td class="small">{{ $s->starts_at?->format('Y-m-d') }} → {{ $s->ends_at?->format('Y-m-d') }}</td><td><span class="badge b-{{ $s->status }}">{{ $s->status }}</span></td></tr>@endforeach
</table></div></div>
@endsection
