@php($compare = $p->compare_price && $p->compare_price > $p->price ? \App\Models\ExchangeRate::toSdg((float) $p->compare_price, $p->price_currency) : null)
<div class="pc">
    <a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="pc-img">
        @if ($p->image())<img src="{{ $p->image() }}" alt="{{ $p->name }}" loading="lazy" decoding="async">@else<span>{{ icon('image', 40) }}</span>@endif
        @if ($compare)<span class="pc-badge">خصم {{ round((1 - $p->priceSdg() / $compare) * 100) }}%</span>@elseif ($p->sales_count > 5)<span class="pc-badge hot">الأكثر مبيعاً</span>@endif
    </a>
    <div class="pc-body">
        @if ($p->category)<span class="pc-cat">{{ $p->category->name }}</span>@endif
        <a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="pc-name">{{ $p->name }}</a>
        <div class="pc-price"><span class="price">{{ money($p->priceSdg()) }}</span>@if ($compare)<span class="old">{{ money($compare) }}</span>@endif</div>
        @if ($p->type === 'variable')
            <a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="pc-btn">اختر الخيارات</a>
        @elseif ($p->availableStock() > 0)
            <form method="post" action="{{ sroute('cart.add') }}">@csrf<input type="hidden" name="product_id" value="{{ $p->id }}"><button class="pc-btn">{{ icon('cart', 16) }} أضف للسلة</button></form>
        @else
            <span class="pc-btn off">نفدت الكمية</span>
        @endif
    </div>
</div>
