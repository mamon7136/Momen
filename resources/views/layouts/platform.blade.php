<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', config('platform.name')) — {{ config('platform.name') }}</title>
    <meta name="description" content="أنشئ متجرك الإلكتروني في السودان خلال دقائق: دفع بنكك، توصيل لكل الولايات، إشعارات واتساب.">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="s-header">
    <div class="container">
        <a href="{{ route('home') }}" class="s-logo">🛍️ {{ config('platform.name') }}</a>
        <nav class="row">
            <a href="{{ route('pricing') }}">الأسعار</a>
            @auth
                <a class="btn sm" href="{{ auth()->user()->is_super_admin ? route('admin.home') : route('dashboard.home') }}">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}">دخول</a>
                <a class="btn sm" href="{{ route('register') }}">أنشئ متجرك مجاناً</a>
            @endauth
        </nav>
    </div>
</header>
<main class="container" style="padding-top:20px;padding-bottom:40px">
    @include('partials.flash')
    @yield('content')
</main>
<footer class="s-footer"><div class="container row between"><span>© {{ date('Y') }} {{ config('platform.name') }} — صُنع في السودان 🇸🇩</span><a href="{{ route('pricing') }}">الباقات</a></div></footer>
</body>
</html>
