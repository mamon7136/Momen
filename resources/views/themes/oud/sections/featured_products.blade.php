@if ($featured->isNotEmpty())
<section class="sec-block">
    <div class="o-title"><h2>{{ $section['title'] ?? 'منتجات مميزة' }}</h2><a href="{{ sroute('products') }}">عرض الكل {{ icon('chev', 14) }}</a></div>
    <div class="ogrid">@foreach ($featured as $p)@include(theme_view('card'))@endforeach</div>
</section>
@endif
