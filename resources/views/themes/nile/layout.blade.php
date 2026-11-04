@extends('themes._base')
@section('header')
<header class="nh">
    <div class="container nh-row">
        <a href="{{ sroute('home') }}" class="nh-logo">@if ($store->logo)<img src="{{ asset('storage/'.$store->logo) }}" alt="{{ $store->name }}">@else<span class="mark">{{ initials($store->name) }}</span>@endif<span>{{ $store->name }}</span></a>
        <form action="{{ sroute('products') }}" class="nh-search no-print"><input name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج..."><button aria-label="بحث">{{ icon('search', 18) }}</button></form>
        <nav class="nh-icons">
            <a href="{{ sroute('track.lookup') }}" title="تتبع طلبك">{{ icon('package', 22) }}</a>
            <a href="{{ sroute('cart') }}" class="cart-ico" title="السلة">{{ icon('cart', 22) }}@if ($cartCount)<span class="n">{{ $cartCount }}</span>@endif</a>
        </nav>
    </div>
    @if ($navCategories->isNotEmpty())
        <nav class="nh-cats"><div class="container"><a href="{{ sroute('products') }}">الكل</a>@foreach ($navCategories as $c)<a href="{{ sroute('category', ['slug' => $c->slug]) }}">{{ $c->name }}</a>@endforeach</div></nav>
    @endif
</header>
@endsection
@section('footer')
<footer class="nf">
    <div class="container nf-grid">
        <div><div class="nh-logo" style="color:#fff">{{ $store->name }}</div><p>{{ $cfg['footer_text'] ?? $store->setting('about', 'متجر إلكتروني موثوق، توصيل لكل الولايات.') }}</p></div>
        <div><h4>روابط</h4><a href="{{ sroute('products') }}">كل المنتجات</a><a href="{{ sroute('track.lookup') }}">تتبع طلبك</a><a href="{{ sroute('cart') }}">سلة التسوق</a></div>
        <div><h4>تواصل معنا</h4>@if ($store->whatsapp)<a href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}">{{ icon('chat', 16) }} واتساب</a>@endif @if ($store->phone)<a href="tel:{{ $store->phone }}">{{ icon('phone', 16) }} {{ $store->phone }}</a>@endif</div>
        <div><h4>طرق الدفع</h4><div class="pay-badges"><span>بنكك</span><span>تحويل بنكي</span><span>عند الاستلام</span></div></div>
    </div>
    <div class="container nf-bottom">© {{ date('Y') }} {{ $store->name }} — مدعوم بواسطة <a href="{{ config('platform.domain') === '*' ? url('/') : config('platform.scheme').'://'.config('platform.domain') }}">{{ config('platform.name') }}</a></div>
</footer>
@endsection
