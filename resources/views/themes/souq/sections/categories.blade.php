@if ($categories->isNotEmpty())
<section class="sec-block"><div class="sq-circles">
    @foreach ($categories as $c)
        @php($img = $c->image ? asset('storage/'.$c->image) : $c->products()->where('is_active', true)->first()?->image())
        <a href="{{ sroute('category', ['slug' => $c->slug]) }}"><span>@if ($img)<img src="{{ $img }}" alt="" loading="lazy">@else{{ icon('folder', 28) }}@endif</span>{{ $c->name }}</a>
    @endforeach
</div></section>
@endif
