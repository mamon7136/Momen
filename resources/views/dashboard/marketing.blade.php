@extends('layouts.dashboard')
@section('title', 'أدوات التسويق')
@section('content')
<div class="grid g2" style="align-items:start">
    <div class="card"><h3>🛒 السلات المتروكة</h3><p class="small muted">عملاء أدخلوا رقمهم في صفحة الدفع ولم يكملوا الطلب.</p>
        <div class="table-wrap"><table><tr><th>الهاتف</th><th>القيمة</th><th>التاريخ</th><th></th></tr>
        @forelse ($carts as $c)<tr><td style="direction:ltr;text-align:right">{{ $c->phone }}</td><td>{{ money($c->total) }}</td><td class="small">{{ $c->updated_at->diffForHumans() }}</td>
            <td>@if ($c->reminded_at)<span class="small muted">تم التذكير</span>@else<form method="post" action="{{ route('dashboard.marketing.remind', $c) }}">@csrf<button class="btn wa sm">تذكير</button></form>@endif</td></tr>
        @empty<tr><td colspan="4" class="muted center">لا توجد سلات متروكة</td></tr>@endforelse</table></div>
    </div>
    <div>
        <div class="card mb"><h3>🔗 روابط المشاركة</h3>
            <label>رابط المتجر</label><input readonly value="{{ $storeUrl }}" onclick="this.select()" style="direction:ltr">
            <div class="row mt"><a class="btn wa sm" href="https://wa.me/?text={{ rawurlencode($storeUrl) }}">واتساب</a><a class="btn sm" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($storeUrl) }}">فيسبوك</a></div>
        </div>
        <div class="card"><h3>⭐ أفضل العملاء</h3>
            @foreach ($topCustomers as $c)<div class="sum-row"><span>{{ $c->name }}</span><span>{{ money($c->total_spent) }}</span></div>@endforeach
            <p class="small muted mt">نصيحة: أنشئ كوبون خاص وأرسله لأفضل عملائك عبر واتساب.</p>
        </div>
    </div>
</div>
@endsection
