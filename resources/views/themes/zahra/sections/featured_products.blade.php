@if ($featured->isNotEmpty())
<section class="sec-block">
    <div class="z-title"><h2>{{ $section['title'] ?? 'منتجات مميزة' }}</h2><a href="{{ sroute('products') }}">عرض الكل {{ icon('chev', 14) }}</a></div>
    <div class="zgrid">@foreach ($featured as $p)@include(theme_view('card'))@endforeach</div>
</section>
@endif
