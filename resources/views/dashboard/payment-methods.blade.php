@extends('layouts.dashboard')
@section('title', 'وسائل الدفع')
@section('content')
<div class="grid g2">
@foreach ($drivers as $key => $label)
    @php($m = $methods[$key] ?? null)
    <form method="post" action="{{ route('dashboard.payment-methods.save') }}" class="card">@csrf
        <input type="hidden" name="driver" value="{{ $key }}">
        <div class="row between"><h3>{{ $label }}</h3><label class="check"><input type="checkbox" name="is_active" value="1" @checked($m?->is_active)> مفعّل</label></div>
        <label>الاسم الظاهر للعميل</label><input name="label" value="{{ $m->label ?? $label }}" required>
        @if (in_array($key, ['mbok', 'bank_transfer']))
            <div class="grid g2"><div><label>رقم الحساب</label><input name="config[account_number]" value="{{ $m->config['account_number'] ?? '' }}" style="direction:ltr"></div>
            <div><label>اسم صاحب الحساب</label><input name="config[account_name]" value="{{ $m->config['account_name'] ?? '' }}"></div></div>
            @if ($key === 'bank_transfer')<label>اسم البنك</label><input name="config[bank_name]" value="{{ $m->config['bank_name'] ?? '' }}">@endif
        @elseif ($key === 'cod')
            <div class="grid g2"><div><label>طلب عربون للطلبات فوق (ج.س)</label><input type="number" name="config[deposit_threshold]" value="{{ $m->config['deposit_threshold'] ?? '' }}"></div>
            <div><label>نسبة العربون %</label><input type="number" name="config[deposit_percent]" value="{{ $m->config['deposit_percent'] ?? '' }}"></div></div>
            <p class="small muted">العربون يُدفع عبر بنكك قبل الشحن لتقليل الطلبات الوهمية.</p>
        @elseif ($key === 'syberpay')
            <div class="grid g2"><div><label>Application ID</label><input name="config[application_id]" value="{{ $m->config['application_id'] ?? '' }}"></div><div><label>Service ID</label><input name="config[service_id]" value="{{ $m->config['service_id'] ?? '' }}"></div></div>
            <label>Salt / Secret</label><input name="config[salt]" type="password" value="{{ $m->config['salt'] ?? '' }}">
            <p class="small muted">رابط الإشعار (Callback): <code>{{ route('webhooks.syberpay', $store) }}</code></p>
        @endif
        <label>رسوم إضافية %</label><input type="number" step="0.1" name="fee_percent" value="{{ $m->config['fee_percent'] ?? 0 }}">
        <label>تعليمات للعميل</label><textarea name="instructions" rows="2">{{ $m->instructions ?? '' }}</textarea>
        <button class="btn mt">حفظ</button>
    </form>
@endforeach
</div>
@endsection
