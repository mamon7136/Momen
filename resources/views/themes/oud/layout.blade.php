@extends('themes._base')
@section('header')
<header class="oh">
    <div class="container oh-row">
        <a href="{{ sroute('track.lookup') }}" class="oh-ic" aria-label="تتبع">{{ icon('package', 20) }}</a>
        <a href="{{ sroute('home') }}" class="oh-logo">@if ($store->logo)<img src="{{ asset('storage/'.$store->logo) }}" alt="">@endif<span>{{ $store->name }}</span><small>✦ عطور وبخور فاخرة ✦</small></a>
        <a href="{{ sroute('cart') }}" class="oh-ic cart-ico" aria-label="السلة">{{ icon('bag', 20) }}@if ($cartCount)<span class="n">{{ $cartCount }}</span>@endif</a>
    </div>
    <nav class="oh-nav"><a href="{{ sroute('products') }}">المجموعة</a>@foreach ($navCategories->take(5) as $c)<a href="{{ sroute('category', ['slug' => $c->slug]) }}">{{ $c->name }}</a>@endforeach</nav>
</header>
@endsection
@section('footer')
<footer class="of">
    <div class="orn">✦ ✦ ✦</div>
    <h3>{{ $store->name }}</h3>
    <p>{{ $cfg['footer_text'] ?? 'روائح أصيلة تُحكى بها الذكريات.' }}</p>
    <div class="of-links"><a href="{{ sroute('products') }}">المجموعة</a><a href="{{ sroute('track.lookup') }}">تتبع الطلب</a>@if ($store->whatsapp)<a href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}">واتساب</a>@endif</div>
    <small>© {{ date('Y') }} · {{ config('platform.name') }}</small>
</footer>
@endsection
