@extends('layouts.store')
@section('title', 'إتمام الطلب')
@section('content')
<h1>إتمام الطلب</h1>
<form method="post" action="{{ sroute('checkout.place') }}" id="co" class="grid g2" style="align-items:start">@csrf
    <div class="card">
        <h3>1. بياناتك</h3>
        <label>الاسم</label><input name="name" value="{{ old('name') }}" required autocomplete="name">
        <label>رقم الهاتف (واتساب)</label><input name="phone" value="{{ old('phone') }}" required inputmode="tel" placeholder="09xxxxxxxx" autocomplete="tel">

        <h3 class="mt">2. طريقة الاستلام</h3>
        <label class="opt"><input type="radio" name="fulfillment" value="delivery" @checked(old('fulfillment','delivery')==='delivery')> 🚚 توصيل</label>
        @if ($branches->isNotEmpty())<label class="opt"><input type="radio" name="fulfillment" value="pickup" @checked(old('fulfillment')==='pickup')> 🏬 استلام من الفرع</label>@endif

        <div id="delivery">
            <label>الولاية</label>
            <select name="state" id="state"><option value="">اختر الولاية</option>@foreach ($zones->pluck('state')->unique() as $s)<option @selected(old('state')===$s)>{{ $s }}</option>@endforeach</select>
            <label>المدينة</label>
            <select name="city" id="city"><option value="">اختر المدينة</option></select>
            <label>الحي / المنطقة</label><input name="area" value="{{ old('area') }}" placeholder="مثال: الرياض، الصحافة، كافوري">
            <label>العنوان بالتفصيل</label><input name="address" value="{{ old('address') }}" placeholder="الشارع، رقم المنزل">
            <label>علامة مميزة</label><input name="landmark" value="{{ old('landmark') }}" placeholder="مثال: جنب الجامع / قصاد الصيدلية">
        </div>
        <div id="pickup" hidden>
            <label>اختر الفرع</label>
            <select name="branch_id">@foreach ($branches as $b)<option value="{{ $b->id }}">{{ $b->name }} — {{ $b->city }} {{ $b->address }}</option>@endforeach</select>
        </div>

        <h3 class="mt">3. طريقة الدفع</h3>
        @foreach ($methods as $m)
            <label class="opt w100" style="justify-content:flex-start"><input type="radio" name="payment_method" value="{{ $m->key() }}" @checked(old('payment_method', $loop->first ? $m->key() : null) === $m->key()) required> {{ $m->label() }}</label>
        @endforeach
        <label>ملاحظات (اختياري)</label><textarea name="notes" rows="2">{{ old('notes') }}</textarea>
    </div>

    <div class="card" style="position:sticky;top:76px">
        <h3>ملخص الطلب</h3>
        @foreach ($lines as $l)<div class="sum-row small"><span>{{ $l['product']->name }} × {{ $l['qty'] }}</span><span>{{ money($l['total']) }}</span></div>@endforeach
        <div class="row mt"><input name="coupon" value="{{ old('coupon') }}" placeholder="كود الخصم" style="flex:1"><button type="button" class="btn ghost" id="apply">تطبيق</button></div>
        <div id="qerr" class="small" style="color:var(--err)"></div>
        <div class="sum-row mt"><span>المجموع</span><span id="q-sub">{{ money($subtotal) }}</span></div>
        <div class="sum-row"><span>الخصم</span><span id="q-dis">0</span></div>
        <div class="sum-row"><span>التوصيل <small id="q-eta" class="muted"></small></span><span id="q-ship">—</span></div>
        <div class="sum-row total"><span>الإجمالي</span><span id="q-total">{{ money($subtotal) }}</span></div>
        <button class="btn w100 mt">تأكيد الطلب ✓</button>
        <p class="small muted center">سيصلك تأكيد الطلب ورابط التتبع على واتساب</p>
    </div>
</form>
@endsection
@section('scripts')
<script>
const cities = @json(config('platform.states')), zones = @json($zones->map->only('state','city')), f = document.getElementById('co');
const fmt = n => Math.round(n).toLocaleString('en') + ' ج.س';
function fillCities(){ const s=f.state.value, c=f.city; const fromZones=zones.filter(z=>z.state===s&&z.city).map(z=>z.city); const list=[...new Set([...(cities[s]||[]),...fromZones])];
  c.innerHTML='<option value="">اختر المدينة</option>'+list.map(x=>`<option ${x===@json(old('city'))?'selected':''}>${x}</option>`).join(''); }
function mode(){ const p=f.fulfillment.value==='pickup'; document.getElementById('pickup').hidden=!p; document.getElementById('delivery').hidden=p; f.state.required=!p; f.address.required=!p; quote(); }
let t; function quote(){ clearTimeout(t); t=setTimeout(async()=>{ const r=await fetch(@json(sroute('checkout.quote')),{method:'POST',headers:{'X-CSRF-TOKEN':f._token.value,'Accept':'application/json'},body:new FormData(f)}); if(!r.ok)return; const q=await r.json();
  document.getElementById('q-sub').textContent=fmt(q.subtotal); document.getElementById('q-dis').textContent=q.discount?('-'+fmt(q.discount)):'0';
  document.getElementById('q-ship').textContent=f.fulfillment.value==='pickup'?'مجاناً':(q.errors.state?'—':fmt(q.shipping)); document.getElementById('q-eta').textContent=q.eta||'';
  document.getElementById('q-total').textContent=fmt(q.total); document.getElementById('qerr').textContent=Object.values(q.errors).join(' • '); },300); }
f.state.addEventListener('change',()=>{fillCities();quote()}); ['city','area','phone','payment_method','fulfillment'].forEach(n=>f.querySelectorAll(`[name=${n}]`).forEach(e=>e.addEventListener('change',n==='fulfillment'?mode:quote)));
document.getElementById('apply').onclick=quote; fillCities(); mode();
</script>
@endsection
