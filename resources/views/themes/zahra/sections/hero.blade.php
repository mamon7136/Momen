@php($heroImg = theme_hero($section))
<section class="z-hero" @if ($heroImg) style="background-image:url('{{ $heroImg }}')" @endif>
    <div class="z-hero-txt"><span class="kicker">مجموعة {{ now()->translatedFormat('F') }}</span><h1>{{ $section['title'] ?? 'جمالك يبدأ من هنا' }}</h1><p>{{ $section['subtitle'] ?? '' }}</p><a href="{{ $section['link'] ?? sroute('products') }}" class="z-btn">اكتشفي المجموعة</a></div>
</section>
