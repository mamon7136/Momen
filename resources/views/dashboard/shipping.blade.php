@extends('layouts.dashboard')
@section('title', 'الشحن والفروع')
@section('content')
<div class="grid g2" style="align-items:start">
<form method="post" action="{{ route('dashboard.shipping.zones.store') }}" class="card">@csrf
    <h3>منطقة توصيل جديدة</h3>
    <p class="small muted">السعر الأكثر تحديداً يطبَّق أولاً (الحي ← المدينة ← الولاية).</p>
    <label>الولاية</label><select name="state" id="zs" required>@foreach (array_keys($states) as $s)<option>{{ $s }}</option>@endforeach</select>
    <label>المدينة (اتركه فارغاً لكل الولاية)</label><input name="city" list="cities"><datalist id="cities">@foreach ($states as $cs)@foreach ($cs as $c)<option value="{{ $c }}">@endforeach @endforeach</datalist>
    <label>الحي (اختياري)</label><input name="area">
    <div class="grid g2"><div><label>سعر التوصيل</label><input type="number" name="price" required></div><div><label>مجاني للطلبات فوق</label><input type="number" name="free_over"></div>
    <div><label>سعر إضافي لكل كجم</label><input type="number" name="price_per_kg"></div><div><label>مدة التوصيل</label><input name="eta" placeholder="نفس اليوم"></div></div>
    <label>شركة الشحن</label><select name="shipping_company_id"><option value="">مندوب المتجر</option>@foreach ($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
    <label class="check"><input type="checkbox" name="cod_allowed" value="1" checked> يسمح بالدفع عند الاستلام</label>
    <button class="btn mt">إضافة</button>
</form>
<div>
    <div class="table-wrap mb"><table><tr><th>المنطقة</th><th>السعر</th><th>المدة</th><th>COD</th><th></th></tr>
    @foreach ($zones as $z)<tr><td>{{ $z->label() }}<div class="small muted">{{ $z->company?->name }}</div></td><td>{{ money($z->price) }} @if ($z->free_over)<div class="small muted">مجاني فوق {{ money($z->free_over) }}</div>@endif</td><td>{{ $z->eta }}</td><td>{{ $z->cod_allowed ? '✓' : '✗' }}</td>
        <td><form method="post" action="{{ route('dashboard.shipping.zones.destroy', $z) }}">@csrf @method('delete')<button class="btn sm danger">حذف</button></form></td></tr>@endforeach
    </table></div>
    <form method="post" action="{{ route('dashboard.shipping.companies.store') }}" class="card mb">@csrf<h3>إضافة شركة توصيل / مندوب</h3>
        <div class="grid g2"><div><label>الاسم</label><input name="name" required></div><div><label>النوع</label><select name="type"><option value="courier">شركة شحن</option><option value="motorcycle">موتر</option><option value="rickshaw">ركشة</option><option value="bus_cargo">شحن بالبصات (بين الولايات)</option></select></div></div>
        <label>رابط التتبع (استخدم {tracking})</label><input name="tracking_url" style="direction:ltr"><button class="btn mt sm">إضافة</button></form>
    <div class="card"><h3>الفروع / المخازن</h3>
        @foreach ($branches as $b)<div class="sum-row"><span>{{ $b->name }} — {{ $b->city }}</span><span class="small">{{ $b->allows_pickup ? 'استلام متاح' : '' }}</span></div>@endforeach
        <form method="post" action="{{ route('dashboard.branches.store') }}" class="mt">@csrf<div class="grid g2"><input name="name" placeholder="اسم الفرع" required><input name="city" placeholder="المدينة"><input name="address" placeholder="العنوان"><input name="phone" placeholder="الهاتف"></div>
            <label class="check"><input type="checkbox" name="allows_pickup" value="1" checked> متاح للاستلام</label><button class="btn sm mt">إضافة فرع</button></form>
    </div>
</div>
</div>
@endsection
