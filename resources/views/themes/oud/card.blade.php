<a href="{{ sroute('product', ['slug' => $p->slug]) }}" class="oc">
    <span class="oc-img">@if ($p->image())<img src="{{ $p->image() }}" alt="{{ $p->name }}" loading="lazy">@endif</span>
    <span class="oc-name">{{ $p->name }}</span>
    <span class="oc-line"></span>
    <span class="oc-price">{{ money($p->priceSdg()) }}</span>
    <span class="oc-btn">اكتشف</span>
</a>
