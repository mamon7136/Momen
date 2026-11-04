@extends(theme_view('layout'))
@section('title', 'سلة التسوق')
@section('content')
<h1>سلة التسوق</h1>
@if ($lines->isEmpty())
    <div class="card center"><p>سلتك فارغة</p><a href="{{ sroute('products') }}" class="btn">تصفح المنتجات</a></div>
@else
<form method="post" action="{{ sroute('cart.update') }}">@csrf
    <div class="card">
        @foreach ($lines as $l)
            <div class="row between" style="border-bottom:1px solid var(--bd);padding:10px 0">
                <div><strong>{{ $l['product']->name }}</strong>@if ($l['variant'])<div class="small muted">{{ $l['variant']->label() }}</div>@endif<div class="price">{{ money($l['price']) }}</div></div>
                <div class="row"><input type="number" name="qty[{{ $l['line'] }}]" value="{{ $l['qty'] }}" min="0" max="99" style="width:80px"><strong>{{ money($l['total']) }}</strong></div>
            </div>
        @endforeach
        <div class="sum-row total"><span>المجموع</span><span>{{ money($subtotal) }}</span></div>
        <div class="row mt"><button class="btn ghost">تحديث السلة</button><a href="{{ sroute('checkout') }}" class="btn" style="flex:1">إتمام الطلب ←</a></div>
    </div>
</form>
@endif
@endsection
