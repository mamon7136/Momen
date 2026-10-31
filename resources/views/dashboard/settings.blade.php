@extends('layouts.dashboard')
@section('title', 'إعدادات المتجر')
@section('content')
<div class="grid g2" style="align-items:start">
<form method="post" action="{{ route('dashboard.settings.update') }}" class="card">@csrf
    <h3>بيانات المتجر</h3>
    <label>اسم المتجر</label><input name="name" value="{{ $store->name }}" required>
    <div class="grid g2"><div><label>الهاتف</label><input name="phone" value="{{ $store->phone }}"></div><div><label>واتساب</label><input name="whatsapp" value="{{ $store->whatsapp }}"></div>
    <div><label>البريد</label><input name="email" value="{{ $store->email }}"></div><div><label>الحد الأدنى للطلب</label><input type="number" name="settings[min_order]" value="{{ $store->setting('min_order') }}"></div></div>
    <label>عن المتجر</label><textarea name="settings[about]" rows="3">{{ $store->setting('about') }}</textarea>
    <label>سياسة الاستبدال والاسترجاع</label><textarea name="settings[return_policy]" rows="3">{{ $store->setting('return_policy') }}</textarea>
    <div class="grid g3"><input name="settings[facebook]" value="{{ $store->setting('facebook') }}" placeholder="Facebook URL"><input name="settings[instagram]" value="{{ $store->setting('instagram') }}" placeholder="Instagram URL"><input name="settings[tiktok]" value="{{ $store->setting('tiktok') }}" placeholder="TikTok URL"></div>
    <button class="btn mt">حفظ</button>
</form>
<div class="card">
    <h3>🌐 الدومين</h3>
    <p>الرابط المجاني: <strong style="direction:ltr;display:inline-block">{{ $store->slug }}.{{ config('platform.domain') }}</strong></p>
    @foreach ($domains as $d)
        <div class="card mb"><div class="row between"><strong style="direction:ltr">{{ $d->domain }}</strong><span class="badge {{ $d->verified_at ? 'b-active' : 'b-pending' }}">{{ $d->verified_at ? 'مفعّل' : 'بانتظار DNS' }}</span></div>
            @unless ($d->verified_at)
                <div class="small mt">أضف أحد السجلات التالية عند مزود الدومين:<br>
                    • <code>CNAME</code> ← <code>{{ config('platform.custom_domain_target') }}</code><br>
                    • أو <code>TXT</code> باسم <code>_platform-verify</code> وقيمة <code>{{ $d->verification_token }}</code></div>
                <form method="post" action="{{ route('dashboard.domains.verify', $d) }}" class="mt">@csrf<button class="btn sm">تحقق الآن</button></form>
            @endunless
        </div>
    @endforeach
    @if ($store->plan?->custom_domain)
        <form method="post" action="{{ route('dashboard.domains.store') }}" class="row">@csrf<input name="domain" placeholder="shop.example.com" style="direction:ltr;flex:1"><button class="btn sm">إضافة دومين</button></form>
    @else
        <div class="alert warn">الدومين الخاص متاح في الباقات الأعلى. <a href="{{ route('dashboard.billing.index') }}">ترقية</a></div>
    @endif
</div>
</div>
@endsection
