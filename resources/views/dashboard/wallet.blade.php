@extends('layouts.dashboard')
@section('title', 'المحفظة')
@section('content')
<div class="grid g2" style="align-items:start">
    <div class="card"><div class="stat-label">الرصيد الحالي</div><div class="stat" style="color:{{ $wallet->balance < 0 ? 'var(--err)' : 'var(--ok)' }}">{{ money($wallet->balance) }}</div>
        <p class="small muted">الرصيد السالب يعني عمولات مستحقة للمنصة (المبالغ التي استلمتها مباشرة عبر بنكك/الكاش). الرصيد الموجب قابل للسحب.</p>
        @if ($wallet->balance > 0)
        <form method="post" action="{{ route('dashboard.wallet.payout') }}">@csrf<label>المبلغ</label><input type="number" name="amount" max="{{ $wallet->balance }}" required>
            <label>طريقة الاستلام</label><select name="method"><option value="mbok">بنكك</option><option value="bank_transfer">تحويل بنكي</option></select>
            <label>رقم الحساب والاسم</label><input name="account" required><button class="btn mt">طلب سحب</button></form>
        @endif
    </div>
    <div class="card"><h3>طلبات السحب</h3>@forelse ($payouts as $p)<div class="sum-row"><span>{{ money($p->amount) }} — {{ $p->created_at->format('Y-m-d') }}</span><span class="badge b-{{ $p->status === 'paid' ? 'paid' : 'pending' }}">{{ $p->status }}</span></div>@empty<p class="muted">لا توجد</p>@endforelse</div>
</div>
<div class="table-wrap mt"><table><tr><th>التاريخ</th><th>النوع</th><th>الوصف</th><th>المبلغ</th><th>الرصيد بعدها</th></tr>
@foreach ($transactions as $t)<tr><td class="small">{{ $t->created_at->format('Y-m-d H:i') }}</td><td>{{ $t->type }}</td><td>{{ $t->description }}</td><td style="color:{{ $t->amount < 0 ? 'var(--err)' : 'var(--ok)' }}">{{ money($t->amount) }}</td><td>{{ money($t->balance_after) }}</td></tr>@endforeach
</table></div>{{ $transactions->links('partials.pagination') }}
@endsection
