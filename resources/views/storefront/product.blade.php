@extends('layouts.store')
@section('title', $product->name)
@section('body-class', 'has-bar')
@section('head')
<meta property="og:title" content="{{ $product->name }}"><meta property="og:type" content="product">
@if ($product->image())<meta property="og:image" content="{{ $product->image() }}">@endif
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Product','name'=>$product->name,'image'=>$product->image(),'offers'=>['@type'=>'Offer','price'=>$product->priceSdg(),'priceCurrency'=>'SDG']], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endsection
@section('content')
<div class="grid g2 mt">
    <div>
        <div class="pcard"><div class="img">@if ($product->image())<img src="{{ $product->image() }}" alt="{{ $product->name }}">@else 🛍️ @endif</div></div>
        @if (count($product->images ?? []) > 1)
            <div class="chips mt">@foreach ($product->images as $img)<img src="{{ asset('storage/'.$img) }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px" loading="lazy" alt="">@endforeach</div>
        @endif
    </div>
    <div class="card">
        @if ($product->category)<a href="{{ sroute('category', ['slug' => $product->category->slug]) }}" class="small">{{ $product->category->name }}</a>@endif
        <h1>{{ $product->name }}</h1>
        <div class="mb"><span class="price" style="font-size:1.4rem" id="price">{{ money($product->priceSdg()) }}</span>
            @if ($product->compare_price > $product->price)<span class="old">{{ money(\App\Models\ExchangeRate::toSdg((float) $product->compare_price, $product->price_currency)) }}</span>@endif</div>
        <form method="post" action="{{ sroute('cart.add') }}" id="buy">@csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            @if ($product->type === 'variable')
                <label>اختر:</label>
                @foreach ($product->variants as $v)
                    @php($stock = $product->availableStock($v->id))
                    <label class="opt" @if ($stock <= 0) style="opacity:.45" @endif><input type="radio" name="variant_id" value="{{ $v->id }}" data-price="{{ money($product->priceSdg($v)) }}" @disabled($stock <= 0) required> {{ $v->label() }}</label>
                @endforeach
            @endif
            @php($inStock = $product->type === 'variable' ? true : $product->availableStock() > 0)
            <label>الكمية</label><input type="number" name="qty" value="1" min="1" max="99" style="max-width:110px">
            <div class="row mt">
                @if ($inStock)
                    <button class="btn">أضف للسلة</button>
                    <button class="btn sec" name="buy_now" value="1">اشتر الآن</button>
                @else
                    <span class="badge b-cancelled">نفدت الكمية</span>
                @endif
            </div>
        </form>
        @if ($product->description)<div class="mt">{!! nl2br(e($product->description)) !!}</div>@endif
        @if ($store->whatsapp)<a class="btn wa sm mt" href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp, 'استفسار عن: '.$product->name.' '.url()->current()) }}">💬 استفسر عبر واتساب</a>@endif
    </div>
</div>
@if ($related->isNotEmpty())<h2 class="mt">منتجات مشابهة</h2><div class="grid g4">@foreach ($related as $p)@include('partials.product-card')@endforeach</div>@endif
<div class="bottom-bar"><span class="price" style="align-self:center">{{ money($product->priceSdg()) }}</span><button form="buy" class="btn" style="flex:1" name="buy_now" value="1">اشتر الآن</button></div>
@endsection
@section('scripts')
<script>document.querySelectorAll('[name=variant_id]').forEach(r=>r.addEventListener('change',()=>document.getElementById('price').textContent=r.dataset.price));</script>
@endsection
