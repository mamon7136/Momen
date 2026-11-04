@php($isAdmin = request()->routeIs('admin.*'))
@php($is = fn (...$names) => collect($names)->contains(fn ($n) => request()->routeIs($n)))
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — {{ config('platform.name') }}</title>
    <script>try{var t=localStorage.getItem('dash-theme');if(t)document.documentElement.dataset.theme=t}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="dash-body">
<input type="checkbox" id="dnav" hidden>
<div class="dl">
    <label for="dnav" class="shade"></label>
    <aside class="ds">
        <div class="ds-brand"><span class="logo">{{ icon('bag', 20) }}</span>{{ config('platform.name') }}</div>
        @if ($isAdmin)
            <nav class="ds-nav">
                <div class="sec">إدارة المنصة</div>
                @foreach ([['admin.home','home','الرئيسية'],['admin.stores','store','المتاجر'],['admin.subscriptions','receipt','الاشتراكات'],['admin.plans','star','الباقات'],['admin.themes','layout','الثيمات'],['admin.payouts','wallet','طلبات السحب'],['admin.settings','settings','الإعدادات']] as [$r,$i,$l])
                    <a href="{{ route($r) }}" class="{{ $is($r) ? 'on' : '' }}">{{ icon($i) }} {{ $l }}</a>
                @endforeach
            </nav>
        @else
            <div class="ds-store">
                <div class="av">@if ($store->logo)<img src="{{ asset('storage/'.$store->logo) }}" alt="">@else{{ initials($store->name) }}@endif</div>
                <div style="min-width:0">
                    <div class="nm">{{ $store->name }}</div>
                    <span class="plan">{{ $store->plan?->name ?? '—' }}</span>
                    <div class="acts">
                        <a href="{{ $store->primaryUrl() }}" target="_blank">زيارة المتجر {{ icon('chev', 12) }}</a>
                        <a href="https://wa.me/?text={{ rawurlencode($store->primaryUrl()) }}" target="_blank" aria-label="مشاركة">{{ icon('share', 13) }}</a>
                    </div>
                </div>
            </div>
            <nav class="ds-nav">
                @foreach ([['dashboard.home','home','الرئيسية',['dashboard.home']],['dashboard.products.index','shirt','المنتجات',['dashboard.products.*']],['dashboard.orders.index','orders','الطلبات',['dashboard.orders.*']],['dashboard.customers.index','users','العملاء',['dashboard.customers.*']],['dashboard.reports.index','chart','التقارير',['dashboard.reports.*']],['dashboard.categories.index','folder','التصنيفات',['dashboard.categories.*']]] as [$r,$i,$l,$m])
                    <a href="{{ route($r) }}" class="{{ $is(...$m) ? 'on' : '' }}">{{ icon($i) }} {{ $l }}</a>
                @endforeach
                <div class="sec">المتجر والتصميم</div>
                <a href="{{ route('dashboard.themes.index') }}" class="{{ $is('dashboard.themes.*') ? 'on' : '' }}">{{ icon('layout') }} متجر الثيمات <span class="new">جديد</span></a>
                <a href="{{ route('dashboard.design.edit') }}" class="{{ $is('dashboard.design.*') ? 'on' : '' }}">{{ icon('palette') }} تخصيص التصميم</a>
                <a href="{{ route('dashboard.settings.edit') }}#domain" class="{{ $is('dashboard.settings.*') ? 'on' : '' }}">{{ icon('globe') }} الدومين والإعدادات</a>
                <div class="sec">التسويق</div>
                <a href="{{ route('dashboard.coupons.index') }}" class="{{ $is('dashboard.coupons.*') ? 'on' : '' }}">{{ icon('tag') }} كوبونات الخصم</a>
                <a href="{{ route('dashboard.marketing.index') }}" class="{{ $is('dashboard.marketing.*') ? 'on' : '' }}">{{ icon('megaphone') }} الأدوات التسويقية</a>
                <div class="sec">التشغيل</div>
                <a href="{{ route('dashboard.shipping.index') }}" class="{{ $is('dashboard.shipping.*') ? 'on' : '' }}">{{ icon('truck') }} الشحن والفروع</a>
                <a href="{{ route('dashboard.payment-methods.index') }}" class="{{ $is('dashboard.payment-methods.*') ? 'on' : '' }}">{{ icon('card') }} وسائل الدفع</a>
                <a href="{{ route('dashboard.wallet.index') }}" class="{{ $is('dashboard.wallet.*') ? 'on' : '' }}">{{ icon('wallet') }} المحفظة</a>
                <div class="sec">الحساب</div>
                <a href="{{ route('dashboard.staff.index') }}" class="{{ $is('dashboard.staff.*') ? 'on' : '' }}">{{ icon('user') }} الموظفين</a>
                <a href="{{ route('dashboard.billing.index') }}" class="{{ $is('dashboard.billing.*') ? 'on' : '' }}">{{ icon('star') }} الاشتراك</a>
                <a href="{{ route('dashboard.apps.index') }}" class="{{ $is('dashboard.apps.*') ? 'on' : '' }}">{{ icon('puzzle') }} متجر التطبيقات</a>
            </nav>
        @endif
    </aside>
    <div class="dm">
        <header class="dt">
            <label for="dnav" class="ibtn dmenu">{{ icon('menu') }}</label>
            @unless ($isAdmin)
                <form class="dt-search" action="{{ route('dashboard.orders.index') }}">
                    <input name="q" value="{{ request('q') }}" placeholder="ابحث برقم الطلب، اسم العميل أو رقم الجوال">
                    <button aria-label="بحث">{{ icon('search', 18) }}</button>
                </form>
            @endunless
            <div class="sp"></div>
            @unless ($isAdmin)
                <a href="{{ route('dashboard.products.create') }}" class="btn dark sm hide-sm">{{ icon('plus', 16) }} منتج جديد</a>
                @php($pendingCount = \App\Models\Payment::where('status', 'review')->count())
                <a href="{{ route('dashboard.orders.index', ['status' => 'payment_review']) }}" class="ibtn" title="إيصالات بانتظار المراجعة">{{ icon('bell', 18) }}@if ($pendingCount)<span class="dot"></span>@endif</a>
            @endunless
            <button class="ibtn" onclick="var h=document.documentElement,n=h.dataset.theme==='dark'?'light':'dark';h.dataset.theme=n;try{localStorage.setItem('dash-theme',n)}catch(e){}" title="الوضع الليلي">{{ icon('moon', 18) }}</button>
            <details class="umenu">
                <summary><span class="uav">{{ initials(auth()->user()->name) }}</span><span class="hide-sm">{{ auth()->user()->name }}</span>{{ icon('chev-down', 16) }}</summary>
                <div class="pop">
                    @if (! $isAdmin && auth()->user()->stores()->count() > 1)
                        <div class="small muted" style="padding:4px 10px">متاجري</div>
                        @foreach (auth()->user()->stores as $s)
                            <form method="post" action="{{ route('dashboard.switch', $s) }}">@csrf<button>{{ icon('store', 16) }} {{ $s->name }} @if ($s->id === $store->id) ✓ @endif</button></form>
                        @endforeach
                    @endif
                    @if (auth()->user()->is_super_admin)<a href="{{ route('admin.home') }}">{{ icon('shield', 16) }} لوحة المنصة</a>@endif
                    @unless ($isAdmin)<a href="{{ route('register.store') }}">{{ icon('plus', 16) }} إنشاء متجر آخر</a>@endunless
                    <form method="post" action="{{ route('logout') }}">@csrf<button>{{ icon('logout', 16) }} تسجيل الخروج</button></form>
                </div>
            </details>
        </header>
        <main class="dc">
            @if (! $isAdmin && isset($store) && $store->status !== 'active')
                <div class="banner amber">{{ icon('alert', 24) }}<div class="grow"><strong>متجرك غير مفعّل حالياً</strong>أكمل دفع الاشتراك ليظهر متجرك للعملاء.</div><a href="{{ route('dashboard.billing.index') }}" class="btn sm dark">إكمال الاشتراك</a></div>
            @endif
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
