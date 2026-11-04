@php($heroImg = theme_hero($section))
<section class="sq-hero">
    <a href="{{ $section['link'] ?? sroute('products') }}" class="sq-main" @if ($heroImg) style="background-image:url('{{ $heroImg }}')" @endif>
        <span class="sq-pill">عروض الأسبوع</span><h1>{{ $section['title'] ?? 'أحدث الأجهزة بأفضل الأسعار' }}</h1><p>{{ $section['subtitle'] ?? '' }}</p><span class="sq-cta">تسوّق الآن {{ icon('chev', 16) }}</span>
    </a>
    <div class="sq-side">
        <a href="{{ sroute('products') }}" class="sq-promo a"><b>توصيل سريع</b><span>داخل الخرطوم خلال 24 ساعة</span>{{ icon('truck', 40) }}</a>
        <a href="{{ sroute('products') }}" class="sq-promo b"><b>ادفع ببنكك</b><span>أو عند الاستلام</span>{{ icon('card', 40) }}</a>
    </div>
</section>
