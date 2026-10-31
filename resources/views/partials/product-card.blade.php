<a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="pcard">
    <div class="img">@if ($p->image())<img src="{{ $p->image() }}" alt="{{ $p->name }}" loading="lazy" decoding="async">@else 🛍️ @endif</div>
    <div class="body">
        <span class="name">{{ $p->name }}</span>
        <span><span class="price">{{ money($p->priceSdg()) }}</span>@if ($p->compare_price && $p->compare_price > $p->price)<span class="old">{{ money(\App\Models\ExchangeRate::toSdg((float) $p->compare_price, $p->price_currency)) }}</span>@endif</span>
    </div>
</a>
