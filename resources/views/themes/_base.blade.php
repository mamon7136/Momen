{{-- Shared storefront shell. Each theme's layout extends this and fills "header" / "footer". --}}
@php($fonts = ['Tajawal' => 'Tajawal:wght@400;500;700;800', 'Cairo' => 'Cairo:wght@400;600;700;800', 'Almarai' => 'Almarai:wght@400;700;800', 'IBM Plex Sans Arabic' => 'IBM+Plex+Sans+Arabic:wght@400;500;600;700', 'Amiri' => 'Amiri:wght@400;700', 'Noto Kufi Arabic' => 'Noto+Kufi+Arabic:wght@400;600;700'])
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>@yield('title', $store->name) | {{ $store->name }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($store->setting('about', $store->name), 150) }}">
    <meta name="theme-color" content="{{ $cfg['colors']['primary'] }}">
    <link rel="manifest" href="{{ sroute('manifest') }}">
    @if ($store->logo)<link rel="icon" href="{{ asset('storage/'.$store->logo) }}">@endif
    @if (isset($fonts[$cfg['font'] ?? '']))
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family={{ $fonts[$cfg['font']] }}&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/'.$slug.'/theme.css') }}">
    <style>:root{--p:{{ $cfg['colors']['primary'] }};--s:{{ $cfg['colors']['secondary'] }};--bg:{{ $cfg['colors']['background'] ?? '#fff' }};--tx:{{ $cfg['colors']['text'] ?? '#111827' }};@if(($cfg['font'] ?? '') !== 'system')--font:"{{ $cfg['font'] }}",system-ui,Tahoma,sans-serif @endif}</style>
    @yield('head')
</head>
<body class="theme-{{ $slug }} @yield('body-class')">
@if (! empty($cfg['announcement']))<div class="announce">{{ $cfg['announcement'] }}</div>@endif
@yield('header')
<main class="@yield('main-class', 'container') s-main">
    @include('partials.flash')
    @yield('content')
</main>
@yield('footer')
@if ($store->whatsapp)
    <a href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}" class="wa-fab no-print" aria-label="واتساب">{{ icon('chat', 26) }}</a>
@endif
<script>
if ('serviceWorker' in navigator) { addEventListener('load', () => navigator.serviceWorker.register('{{ sroute('sw') }}', {scope: '{{ rtrim(parse_url(sroute('home'), PHP_URL_PATH) ?: '/', '/') }}/'}).catch(() => {})); }
</script>
@yield('scripts')
</body>
</html>
