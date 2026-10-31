@extends('layouts.dashboard')
@section('title', 'الموظفين والصلاحيات')
@section('content')
<div class="grid g2" style="align-items:start">
<form method="post" action="{{ route('dashboard.staff.store') }}" class="card">@csrf<h3>إضافة موظف</h3>
    <label>الاسم</label><input name="name" required><label>رقم الهاتف (واتساب)</label><input name="phone" required>
    <label>الدور</label><select name="role">@foreach (['manager','staff','accountant','shipping'] as $r)<option value="{{ $r }}">{{ $roles[$r] }} — {{ implode('، ', config('platform.roles.'.$r)) }}</option>@endforeach</select>
    <button class="btn mt">إضافة وإرسال بيانات الدخول</button></form>
<div class="table-wrap"><table><tr><th>الاسم</th><th>الهاتف</th><th>الدور</th><th></th></tr>
@foreach ($staff as $u)<tr><td>{{ $u->name }}</td><td style="direction:ltr;text-align:right">{{ $u->phone }}</td><td>{{ $roles[$u->pivot->role] ?? $u->pivot->role }}</td>
<td>@if ($u->id !== $store->owner_id)<form method="post" action="{{ route('dashboard.staff.destroy', $u) }}">@csrf @method('delete')<button class="btn sm danger">حذف</button></form>@endif</td></tr>@endforeach
</table></div></div>
@endsection
