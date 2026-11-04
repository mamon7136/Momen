@if ($categories->isNotEmpty())
<section class="o-cats">@foreach ($categories as $c)<a href="{{ sroute('category', ['slug' => $c->slug]) }}">{{ $c->name }}</a>@endforeach</section>
@endif
