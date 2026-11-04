@php($compare = $p->compare_price && $p->compare_price > $p->price ? \App\Models\ExchangeRate::toSdg((float) $p->compare_price, $p->price_currency) : null)
<a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="zc">
    <span class="zc-img">@if ($p->image())<img src="{{ $p->image() }}" alt="{{ $p->name }}" loading="lazy">@endif @if ($compare)<span class="zc-tag">تخفيض</span>@elseif ($p->created_at?->gt(now()->subDays(14)))<span class="zc-tag">جديد</span>@endif<span class="zc-quick">عرض القطعة</span></span>
    <span class="zc-name">{{ $p->name }}</span>
    <span class="zc-price">{{ money($p->priceSdg()) }}@if ($compare)<s>{{ money($compare) }}</s>@endif</span>
</a>
