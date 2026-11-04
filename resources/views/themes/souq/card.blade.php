@php($compare = $p->compare_price && $p->compare_price > $p->price ? \App\Models\ExchangeRate::toSdg((float) $p->compare_price, $p->price_currency) : null)
<div class="sc">
    @if ($compare)<span class="sc-off">-{{ round((1 - $p->priceSdg() / $compare) * 100) }}%</span>@endif
    <a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="sc-img">@if ($p->image())<img src="{{ $p->image() }}" alt="{{ $p->name }}" loading="lazy">@else{{ icon('image', 36) }}@endif</a>
    <a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="sc-name">{{ $p->name }}</a>
    <div class="sc-meta">@if ($p->sales_count)<span>{{ icon('star', 12) }} {{ $p->sales_count }} مبيعات</span>@endif @if ($p->availableStock() > 0 && $p->availableStock() <= 5)<span class="sc-left">باقي {{ $p->availableStock() }} فقط</span>@endif</div>
    <div class="sc-price"><b>{{ money($p->priceSdg()) }}</b>@if ($compare)<s>{{ money($compare) }}</s>@endif</div>
    @if ($p->type === 'variable')<a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="sc-btn">اختر</a>
    @elseif ($p->availableStock() > 0)<form method="post" action="{{ sroute('cart.add') }}">@csrf<input type="hidden" name="product_id" value="{{ $p->id }}"><button class="sc-btn">{{ icon('cart', 16) }} أضف للسلة</button></form>
    @else<span class="sc-btn off">غير متوفر</span>@endif
</div>
