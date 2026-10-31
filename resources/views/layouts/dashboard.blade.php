<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — {{ config('platform.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<input type="checkbox" id="nav-toggle" hidden>
<div class="dash">
    <aside class="side">
        @php($isAdmin = request()->routeIs('admin.*'))
        @if ($isAdmin)
            <div class="brand">⚙️ إدارة المنصة</div>
            @foreach ([['admin.home','📊','الرئيسية'],['admin.stores','🏪','المتاجر'],['admin.subscriptions','💳','الاشتراكات'],['admin.plans','📦','الباقات'],['admin.themes','🎨','الثيمات'],['admin.payouts','💰','طلبات السحب'],['admin.settings','🔧','الإعدادات']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="{{ request()->routeIs($r) ? 'on' : '' }}">{{ $i }} {{ $l }}</a>
            @endforeach
        @else
            <div class="brand">🛍️ {{ $store->name }}</div>
            <a href="{{ $store->primaryUrl() }}" target="_blank" class="small">🔗 زيارة المتجر</a>
            <div class="sec">المبيعات</div>
            @foreach ([['dashboard.home','📊','الرئيسية'],['dashboard.orders.index','🧾','الطلبات'],['dashboard.customers.index','👥','العملاء'],['dashboard.reports.index','📈','التقارير'],['dashboard.wallet.index','💰','المحفظة']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="{{ request()->routeIs(str_replace('.index','.*',$r)) ? 'on' : '' }}">{{ $i }} {{ $l }}</a>
            @endforeach
            <div class="sec">الكتالوج</div>
            @foreach ([['dashboard.products.index','📦','المنتجات'],['dashboard.categories.index','🗂️','التصنيفات']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="{{ request()->routeIs(str_replace('.index','.*',$r)) ? 'on' : '' }}">{{ $i }} {{ $l }}</a>
            @endforeach
            <div class="sec">التسويق والتشغيل</div>
            @foreach ([['dashboard.coupons.index','🏷️','الكوبونات'],['dashboard.marketing.index','📣','التسويق'],['dashboard.shipping.index','🚚','الشحن والفروع'],['dashboard.payment-methods.index','💳','وسائل الدفع'],['dashboard.design.edit','🎨','تصميم المتجر']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="{{ request()->routeIs(str_replace(['.index','.edit'],'.*',$r)) ? 'on' : '' }}">{{ $i }} {{ $l }}</a>
            @endforeach
            <div class="sec">الحساب</div>
            @foreach ([['dashboard.settings.edit','⚙️','الإعدادات والدومين'],['dashboard.staff.index','🧑‍💼','الموظفين'],['dashboard.billing.index','⭐','الاشتراك'],['dashboard.apps.index','🧩','التطبيقات']] as [$r,$i,$l])
                <a href="{{ route($r) }}" class="{{ request()->routeIs(str_replace(['.index','.edit'],'.*',$r)) ? 'on' : '' }}">{{ $i }} {{ $l }}</a>
            @endforeach
        @endif
    </aside>
    <div class="main">
        <div class="topbar">
            <label for="nav-toggle" class="btn ghost sm menu-btn">☰</label>
            <strong>@yield('title')</strong>
            <div class="row">
                @if (! $isAdmin && auth()->user()->stores()->count() > 1)
                    <form method="post" action="" onchange="this.action='{{ url('dashboard/switch') }}/'+this.s.value;this.submit()">@csrf
                        <select name="s" style="min-height:34px;padding:4px">@foreach (auth()->user()->stores as $s)<option value="{{ $s->id }}" @selected($s->id === $store->id)>{{ $s->name }}</option>@endforeach</select>
                    </form>
                @endif
                <span class="small muted">{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="btn ghost sm">خروج</button></form>
            </div>
        </div>
        <div class="content">
            @if (! $isAdmin && isset($store) && $store->status !== 'active')
                <div class="alert warn">⚠️ متجرك غير مفعل حالياً ({{ $store->status }}). <a href="{{ route('dashboard.billing.index') }}">أكمل الاشتراك</a> ليظهر للعملاء.</div>
            @endif
            @include('partials.flash')
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
