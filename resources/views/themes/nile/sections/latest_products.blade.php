<section class="sec-block">
    <div class="sec-title"><h2>{{ $section['title'] ?? 'وصل حديثاً' }}</h2><a href="{{ sroute('products') }}">عرض الكل {{ icon('chev', 14) }}</a></div>
    <div class="pgrid">@forelse ($latest as $p)@include(theme_view('card'))@empty<p class="muted">لا توجد منتجات بعد.</p>@endforelse</div>
</section>
