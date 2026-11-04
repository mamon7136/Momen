@extends(theme_view('layout'))
@section('title', 'تتبع طلبك')
@section('content')
<div class="card" style="max-width:420px;margin:20px auto">
    <h2>تتبع طلبك</h2>
    <form><label>رقم الطلب</label><input name="number" required value="{{ request('number') }}"><label>رقم الهاتف</label><input name="phone" required value="{{ request('phone') }}"><button class="btn w100 mt">بحث</button></form>
</div>
@endsection
