@extends('themes._base')
@section('header')
<header class="zh">
    <div class="zh-row">
        <nav class="zh-nav"><a href="{{ sroute('products') }}">المتجر</a>@foreach ($navCategories->take(3) as $c)<a href="{{ sroute('category', ['slug' => $c->slug]) }}">{{ $c->name }}</a>@endforeach</nav>
        <a href="{{ sroute('home') }}" class="zh-logo">@if ($store->logo)<img src="{{ asset('storage/'.$store->logo) }}" alt="{{ $store->name }}">@else{{ $store->name }}@endif</a>
        <nav class="zh-icons">
            <details class="zh-sd"><summary aria-label="بحث">{{ icon('search', 20) }}</summary><form action="{{ sroute('products') }}"><input name="q" placeholder="ابحثي عن قطعة..." autofocus></form></details>
            <a href="{{ sroute('track.lookup') }}" aria-label="تتبع">{{ icon('user', 20) }}</a>
            <a href="{{ sroute('cart') }}" class="cart-ico" aria-label="السلة">{{ icon('bag', 20) }}@if ($cartCount)<span class="n">{{ $cartCount }}</span>@endif</a>
        </nav>
    </div>
</header>
@endsection
@section('footer')
<footer class="zf">
    <div class="zf-news"><h3>{{ $store->name }}</h3><p>{{ $cfg['footer_text'] ?? 'أناقة تليق بك — قطع مختارة بعناية.' }}</p></div>
    <div class="zf-links"><a href="{{ sroute('products') }}">المتجر</a><a href="{{ sroute('track.lookup') }}">تتبع الطلب</a>@if ($store->whatsapp)<a href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}">واتساب</a>@endif @if ($store->setting('instagram'))<a href="{{ $store->setting('instagram') }}">انستغرام</a>@endif</div>
    <div class="zf-copy">© {{ date('Y') }} {{ $store->name }} · {{ config('platform.name') }}</div>
</footer>
@endsection
