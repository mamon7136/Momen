@if ($categories->isNotEmpty())
<section class="sec-block">
    <div class="sec-title"><h2>{{ $section['title'] ?? 'تسوّق حسب التصنيف' }}</h2></div>
    <div class="cat-grid">
        @foreach ($categories as $c)
            @php($img = $c->image ? asset('storage/'.$c->image) : $c->products()->where('is_active', true)->first()?->image())
            <a href="{{ sroute('category', ['slug' => $c->slug]) }}" class="cat-tile"><span class="ci">@if ($img)<img src="{{ $img }}" alt="" loading="lazy">@else{{ icon('folder', 28) }}@endif</span><span>{{ $c->name }}</span></a>
        @endforeach
    </div>
</section>
@endif
