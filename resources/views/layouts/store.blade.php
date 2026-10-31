@php($cfg = $store->themeConfig())
@php($cartCount = app(\App\Services\CartService::class)->count())
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>@yield('title', $store->name) | {{ $store->name }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($store->setting('about', $store->name), 150) }}">
    <meta name="theme-color" content="{{ $cfg['colors']['primary'] }}">
    <link rel="manifest" href="{{ sroute('manifest') }}">
    @if ($store->logo)<link rel="icon" href="{{ asset('storage/'.$store->logo) }}">@endif
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>:root{--p:{{ $cfg['colors']['primary'] }};--s:{{ $cfg['colors']['secondary'] }};--bg:{{ $cfg['colors']['background'] ?? '#fff' }};--tx:{{ $cfg['colors']['text'] ?? '#111827' }};@if(($cfg['font'] ?? '') !== 'system')--font:"{{ $cfg['font'] }}",system-ui,Tahoma,sans-serif @endif}</style>
    @yield('head')
</head>
<body class="@yield('body-class')">
@if (! empty($cfg['announcement']))<div class="announce">{{ $cfg['announcement'] }}</div>@endif
<header class="s-header">
    <div class="container">
        <a href="{{ sroute('home') }}" class="s-logo">
            @if ($store->logo)<img src="{{ asset('storage/'.$store->logo) }}" alt="{{ $store->name }}" width="38" height="38">@endif
            {{ $store->name }}
        </a>
        <form action="{{ sroute('products') }}" style="flex:1;max-width:380px" class="no-print"><input name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج..." style="min-height:38px"></form>
        <a href="{{ sroute('cart') }}" class="cart-fab btn ghost sm">🛒 @if ($cartCount)<span class="n">{{ $cartCount }}</span>@endif</a>
    </div>
</header>
<main class="container" style="padding-top:12px">
    @include('partials.flash')
    @yield('content')
</main>
<footer class="s-footer">
    <div class="container grid g3">
        <div><strong style="color:#fff">{{ $store->name }}</strong><p>{{ $cfg['footer_text'] ?? $store->setting('about') }}</p></div>
        <div>
            <a href="{{ sroute('track.lookup') }}">تتبع طلبك</a><br>
            @if ($store->whatsapp)<a href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp, 'مرحباً، لدي استفسار') }}">تواصل عبر واتساب</a>@endif
        </div>
        <div class="small">مدعوم بواسطة <a href="{{ config('platform.scheme').'://'.config('platform.domain') }}">{{ config('platform.name') }}</a></div>
    </div>
</footer>
@if ($store->whatsapp)
<a href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}" class="btn wa no-print" style="position:fixed;bottom:86px;left:16px;border-radius:50%;width:54px;height:54px;padding:0;font-size:1.5rem;z-index:8" aria-label="واتساب">💬</a>
@endif
<script>
if ('serviceWorker' in navigator) { addEventListener('load', () => navigator.serviceWorker.register('{{ sroute('sw') }}', {scope: '{{ rtrim(parse_url(sroute('home'), PHP_URL_PATH) ?: '/', '/') }}/'}).catch(() => {})); }
</script>
@yield('scripts')
</body>
</html>
