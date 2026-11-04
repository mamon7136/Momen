@php($heroImg = theme_hero($section))
<section class="n-hero" @if ($heroImg) style="background-image:url('{{ $heroImg }}')" @endif>
    <div class="n-hero-txt">
        <h1>{{ $section['title'] ?? $store->name }}</h1>
        <p>{{ $section['subtitle'] ?? '' }}</p>
        <a href="{{ $section['link'] ?? sroute('products') }}" class="btn sec">تسوّق الآن {{ icon('chev', 16) }}</a>
    </div>
</section>
