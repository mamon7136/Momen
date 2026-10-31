@extends('layouts.dashboard')
@section('title', 'كوبونات الخصم')
@section('content')
<form method="post" action="{{ route('dashboard.coupons.store') }}" class="card mb">@csrf
    <h3>كوبون جديد</h3>
    <div class="grid g3">
        <div><label>الكود</label><input name="code" required style="text-transform:uppercase" placeholder="EID25"></div>
        <div><label>النوع</label><select name="type"><option value="percent">نسبة %</option><option value="fixed">مبلغ ثابت</option><option value="free_shipping">توصيل مجاني</option></select></div>
        <div><label>القيمة</label><input type="number" step="0.01" name="value"></div>
        <div><label>الحد الأدنى للطلب</label><input type="number" name="min_order"></div>
        <div><label>أقصى خصم</label><input type="number" name="max_discount"></div>
        <div><label>عدد الاستخدامات</label><input type="number" name="usage_limit"></div>
        <div><label>لكل عميل</label><input type="number" name="per_customer_limit"></div>
        <div><label>ينتهي في</label><input type="date" name="expires_at"></div>
    </div>
    <button class="btn mt">إنشاء</button>
</form>
<div class="table-wrap"><table><tr><th>الكود</th><th>الخصم</th><th>الاستخدام</th><th>الانتهاء</th><th>الحالة</th><th></th></tr>
@foreach ($coupons as $c)
<tr><td><strong>{{ $c->code }}</strong></td><td>{{ $c->type === 'percent' ? (float)$c->value.'%' : ($c->type === 'fixed' ? money($c->value) : 'توصيل مجاني') }}</td><td>{{ $c->used_count }} / {{ $c->usage_limit ?? '∞' }}</td><td>{{ $c->expires_at?->format('Y-m-d') ?? '—' }}</td>
<td><form method="post" action="{{ route('dashboard.coupons.toggle', $c) }}">@csrf<button class="badge {{ $c->is_active ? 'b-active' : 'b-cancelled' }}" style="border:0;cursor:pointer">{{ $c->is_active ? 'مفعل' : 'متوقف' }}</button></form></td>
<td><form method="post" action="{{ route('dashboard.coupons.destroy', $c) }}">@csrf @method('delete')<button class="btn sm danger">حذف</button></form></td></tr>
@endforeach
</table></div>
@endsection
