@extends('layouts.dashboard')
@section('title', 'إعدادات المنصة')
@section('content')
<div class="grid g2" style="align-items:start">
<div class="card"><h3>💱 أسعار الصرف (مقابل الجنيه)</h3>
    @foreach ($rates as $r)<div class="sum-row"><span>{{ $r->currency }}</span><span>{{ number_format($r->rate_to_sdg, 2) }} ج.س <small class="muted">{{ $r->updated_at->diffForHumans() }}</small></span></div>@endforeach
    <form method="post" action="{{ route('admin.rates.save') }}" class="row mt">@csrf<input name="currency" placeholder="USD" style="width:90px" required><input type="number" step="0.0001" name="rate_to_sdg" placeholder="السعر" required style="flex:1"><button class="btn sm">تحديث</button></form>
    <p class="small muted">المنتجات المسعّرة بالدولار تتحدث أسعارها تلقائياً.</p>
</div>
<div class="card"><h3>🚚 شركات الشحن المتاحة لكل المتاجر</h3>
    @foreach ($companies as $c)<div class="sum-row"><span>{{ $c->name }}</span><span>{{ $c->type }}</span></div>@endforeach
    <form method="post" action="{{ route('admin.companies.save') }}" class="mt">@csrf<input name="name" placeholder="اسم الشركة" required><select name="type" class="mt"><option value="courier">شركة شحن</option><option value="motorcycle">مواتر</option><option value="rickshaw">ركشات</option><option value="bus_cargo">شحن بالبصات</option></select><input name="tracking_url" placeholder="https://.../track/{tracking}" class="mt" style="direction:ltr"><button class="btn sm mt">إضافة</button></form>
</div>
</div>
@endsection
