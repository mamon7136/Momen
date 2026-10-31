@extends('layouts.dashboard')
@section('title', 'الثيمات')
@section('content')
<div class="grid g3 mb">@foreach ($themes as $t)<div class="card"><h3>{{ $t->name }}</h3><div class="row"><span style="width:30px;height:30px;border-radius:8px;background:{{ $t->default_settings['colors']['primary'] ?? '#000' }}"></span><span style="width:30px;height:30px;border-radius:8px;background:{{ $t->default_settings['colors']['secondary'] ?? '#000' }}"></span></div></div>@endforeach</div>
<form method="post" action="{{ route('admin.themes.save') }}" class="card" style="max-width:480px">@csrf<h3>ثيم جديد / تعديل</h3>
<label>المعرف</label><input name="slug" required><label>الاسم</label><input name="name" required>
<div class="grid g2"><div><label>الأساسي</label><input type="color" name="primary" value="#0f766e"></div><div><label>الثانوي</label><input type="color" name="secondary" value="#f59e0b"></div></div><button class="btn mt">حفظ</button></form>
@endsection
