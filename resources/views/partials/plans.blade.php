<div class="grid g3">
@foreach ($plans as $plan)
    <div class="card" style="{{ $plan->slug === 'pro' ? 'border:2px solid var(--p)' : '' }}">
        <h3>{{ $plan->name }} @if ($plan->slug === 'pro')<span class="badge">الأكثر طلباً</span>@endif</h3>
        <div class="stat">{{ $plan->isFree() ? 'مجاناً' : money($plan->price_monthly) }}<span class="small muted">{{ $plan->isFree() ? '' : ' / شهرياً' }}</span></div>
        @unless ($plan->isFree())<div class="small muted">أو {{ money($plan->price_yearly) }} سنوياً</div>@endunless
        <p class="muted small">{{ $plan->description }}</p>
        <ul class="small" style="padding-inline-start:18px">
            <li>{{ $plan->max_products ? $plan->max_products.' منتج' : 'منتجات غير محدودة' }}</li>
            <li>{{ $plan->max_staff ? $plan->max_staff.' موظف' : 'موظفين بلا حدود' }}</li>
            <li>عمولة {{ (float) $plan->commission_rate }}% على المبيعات</li>
            @if ($plan->custom_domain)<li>دومين خاص (.com / .sd)</li>@endif
            @foreach ($plan->features ?? [] as $f)<li>{{ $f }}</li>@endforeach
        </ul>
        <a class="btn w100 {{ $plan->slug === 'pro' ? '' : 'out' }}" href="{{ route('register', ['plan' => $plan->id]) }}">ابدأ الآن</a>
    </div>
@endforeach
</div>
