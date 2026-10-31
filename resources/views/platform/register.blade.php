@extends('layouts.platform')
@section('title', 'أنشئ متجرك')
@section('content')
@php($existing = $existingUser ?? null)
<div class="card" style="max-width:760px;margin:0 auto">
    <h1>أنشئ متجرك الإلكتروني</h1>
    <form method="post" action="{{ $existing ? route('register.store') : route('register') }}">@csrf
        @unless ($existing)
            <h3>1. حسابك</h3>
            <div class="grid g2">
                <div><label>الاسم الكامل</label><input name="name" value="{{ old('name') }}" required></div>
                <div><label>رقم الهاتف (واتساب)</label><input name="phone" value="{{ old('phone') }}" required inputmode="tel" placeholder="09xxxxxxxx"></div>
                <div><label>البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email') }}" required></div>
                <div><label>كلمة المرور</label><input type="password" name="password" required minlength="8"></div>
                <div><label>تأكيد كلمة المرور</label><input type="password" name="password_confirmation" required></div>
            </div>
        @endunless
        <h3 class="mt">{{ $existing ? '1' : '2' }}. متجرك</h3>
        <div class="grid g2">
            <div><label>اسم المتجر</label><input name="store_name" value="{{ old('store_name') }}" required></div>
            <div><label>رابط المتجر</label>
                <div class="row" style="flex-wrap:nowrap;direction:ltr"><input name="slug" id="slug" value="{{ old('slug') }}" required pattern="[a-z0-9][a-z0-9-]{2,30}" placeholder="mystore" style="direction:ltr"><span class="small muted">.{{ config('platform.domain') }}</span></div>
                <div id="slug-msg" class="small"></div>
            </div>
            <div><label>الولاية</label><select name="state">@foreach (array_keys(config('platform.states')) as $s)<option @selected(old('state')===$s)>{{ $s }}</option>@endforeach</select></div>
        </div>
        <h3 class="mt">{{ $existing ? '2' : '3' }}. الباقة</h3>
        @foreach ($plans as $plan)
            <label class="opt"><input type="radio" name="plan_id" value="{{ $plan->id }}" required @checked(old('plan_id', $selectedPlan) == $plan->id || (! old('plan_id') && ! $selectedPlan && $loop->first))>
                {{ $plan->name }} — {{ $plan->isFree() ? 'مجاناً' : money($plan->price_monthly).'/شهر' }} @if ($plan->trial_days) <span class="badge">تجربة {{ $plan->trial_days }} يوم</span>@endif</label>
        @endforeach
        <div class="row mt"><label class="check"><input type="radio" name="cycle" value="monthly" checked> شهري</label><label class="check"><input type="radio" name="cycle" value="yearly" @checked(old('cycle')==='yearly')> سنوي (وفر شهرين)</label></div>
        <h3 class="mt">{{ $existing ? '3' : '4' }}. الثيم</h3>
        <div class="grid g3">
            @foreach ($themes as $t)
                <label class="opt" style="display:block"><input type="radio" name="theme_id" value="{{ $t->id }}" @checked(old('theme_id') == $t->id || $loop->first)>
                    <span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:{{ $t->default_settings['colors']['primary'] ?? '#0f766e' }};vertical-align:middle"></span> {{ $t->name }}</label>
            @endforeach
        </div>
        <button class="btn w100 mt">إنشاء المتجر 🚀</button>
    </form>
</div>
<script>
const s=document.getElementById('slug'),m=document.getElementById('slug-msg');let t;
s.addEventListener('input',()=>{s.value=s.value.toLowerCase().replace(/[^a-z0-9-]/g,'');clearTimeout(t);t=setTimeout(async()=>{if(s.value.length<3)return;const r=await (await fetch(@json(route('slug.check'))+'?slug='+s.value)).json();m.textContent=r.available?'✅ الرابط متاح':'❌ الرابط غير متاح';m.style.color=r.available?'green':'red'},350)});
</script>
@endsection
