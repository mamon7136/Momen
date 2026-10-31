@extends('layouts.platform')
@section('title', 'تسجيل الدخول')
@section('content')
<div class="card" style="max-width:420px;margin:20px auto">
    <h2>تسجيل الدخول</h2>
    <form method="post" action="{{ route('login') }}">@csrf
        <label>رقم الهاتف أو البريد الإلكتروني</label><input name="login" value="{{ old('login') }}" required autofocus>
        @if (session('otp_sent'))
            <label>رمز التحقق (واتساب)</label><input name="otp" inputmode="numeric" maxlength="6" required>
        @else
            <label>كلمة المرور</label><input type="password" name="password">
            <label class="check"><input type="checkbox" name="remember" value="1" checked> تذكرني</label>
        @endif
        <button class="btn w100 mt">دخول</button>
    </form>
    @unless (session('otp_sent'))
    <form method="post" action="{{ route('login.otp') }}" class="mt" onsubmit="this.phone.value=document.querySelector('[name=login]').value">@csrf
        <input type="hidden" name="phone"><button class="btn ghost w100">💬 الدخول برمز عبر واتساب</button>
    </form>
    @endunless
    <p class="center small mt">ليس لديك متجر؟ <a href="{{ route('register') }}">أنشئ متجرك</a></p>
</div>
@endsection
