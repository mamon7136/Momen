@extends('themes._base')
@section('header')
<div class="sq-top"><div class="container"><span>{{ icon('truck', 14) }} توصيل لكل ولايات السودان</span><span>{{ icon('phone', 14) }} {{ $store->phone }}</span></div></div>
<header class="sq-h">
    <div class="container sq-row">
        <a href="{{ sroute('home') }}" class="sq-logo">@if ($store->logo)<img src="{{ asset('storage/'.$store->logo) }}" alt="">@endif{{ $store->name }}</a>
        <form action="{{ sroute('products') }}" class="sq-search"><input name="q" value="{{ request('q') }}" placeholder="عن ماذا تبحث اليوم؟"><button>{{ icon('search', 20) }}</button></form>
        <a href="{{ sroute('track.lookup') }}" class="sq-act">{{ icon('package', 22) }}<span>طلباتي</span></a>
        <a href="{{ sroute('cart') }}" class="sq-act cart-ico">{{ icon('cart', 22) }}<span>السلة</span>@if ($cartCount)<span class="n">{{ $cartCount }}</span>@endif</a>
    </div>
    <nav class="sq-cats"><div class="container">{{ icon('menu', 18) }}<a href="{{ sroute('products') }}">كل الأقسام</a>@foreach ($navCategories as $c)<a href="{{ sroute('category', ['slug' => $c->slug]) }}">{{ $c->name }}</a>@endforeach</div></nav>
</header>
@endsection
@section('footer')
<footer class="sq-f">
    <div class="container nf-grid">
        <div><b>{{ $store->name }}</b><p>{{ $cfg['footer_text'] ?? 'كل ما تحتاجه بأفضل الأسعار وضمان الجودة.' }}</p></div>
        <div><h4>خدمة العملاء</h4><a href="{{ sroute('track.lookup') }}">تتبع طلبك</a>@if ($store->whatsapp)<a href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}">واتساب</a>@endif</div>
        <div><h4>الأقسام</h4>@foreach ($navCategories->take(4) as $c)<a href="{{ sroute('category', ['slug' => $c->slug]) }}">{{ $c->name }}</a>@endforeach</div>
        <div><h4>ادفع بأمان</h4><div class="pay-badges"><span>بنكك</span><span>SyberPay</span><span>كاش</span></div></div>
    </div>
    <div class="container nf-bottom">© {{ date('Y') }} {{ $store->name }} · {{ config('platform.name') }}</div>
</footer>
@endsection
