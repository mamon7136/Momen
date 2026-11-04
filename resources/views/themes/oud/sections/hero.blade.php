@php($heroImg = theme_hero($section))
<section class="o-hero" @if ($heroImg) style="background-image:linear-gradient(90deg,rgba(18,16,18,.2),rgba(18,16,18,.85) 65%),url('{{ $heroImg }}')" @endif>
    <div class="o-hero-txt"><div class="orn">✦</div><h1>{{ $section['title'] ?? 'فخامة تُروى' }}</h1><p>{{ $section['subtitle'] ?? '' }}</p><a href="{{ $section['link'] ?? sroute('products') }}" class="o-btn">اكتشف المجموعة</a></div>
</section>
