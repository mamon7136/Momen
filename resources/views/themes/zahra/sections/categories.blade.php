@if ($categories->isNotEmpty())
<section class="z-cats">
    @foreach ($categories->take(4) as $c)
        @php($img = $c->image ? asset('storage/'.$c->image) : $c->products()->where('is_active', true)->first()?->image())
        <a href="{{ sroute('category', ['slug' => $c->slug]) }}" class="z-cat" @if ($img) style="background-image:url('{{ $img }}')" @endif><span>{{ $c->name }}</span></a>
    @endforeach
</section>
@endif
