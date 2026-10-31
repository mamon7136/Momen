@extends('layouts.dashboard')
@section('title', 'الباقات')
@section('content')
<div class="grid g2">
@foreach ($plans->push(new \App\Models\Plan(['is_active' => true])) as $p)
<form method="post" action="{{ route('admin.plans.save') }}" class="card">@csrf<input type="hidden" name="id" value="{{ $p->id }}">
    <h3>{{ $p->name ?? 'باقة جديدة' }}</h3>
    <div class="grid g2"><div><label>المعرف</label><input name="slug" value="{{ $p->slug }}" required></div><div><label>الاسم</label><input name="name" value="{{ $p->name }}" required></div>
    <div><label>شهري</label><input type="number" name="price_monthly" value="{{ $p->price_monthly ?? 0 }}"></div><div><label>سنوي</label><input type="number" name="price_yearly" value="{{ $p->price_yearly ?? 0 }}"></div>
    <div><label>العمولة %</label><input type="number" step="0.1" name="commission_rate" value="{{ $p->commission_rate ?? 0 }}"></div><div><label>أيام التجربة</label><input type="number" name="trial_days" value="{{ $p->trial_days ?? 0 }}"></div>
    <div><label>حد المنتجات</label><input type="number" name="max_products" value="{{ $p->max_products }}"></div><div><label>حد الموظفين</label><input type="number" name="max_staff" value="{{ $p->max_staff }}"></div>
    <div><label>حد الفروع</label><input type="number" name="max_branches" value="{{ $p->max_branches }}"></div><div><label>الترتيب</label><input type="number" name="sort" value="{{ $p->sort ?? 0 }}"></div></div>
    <label>الوصف</label><input name="description" value="{{ $p->description }}">
    <label>المميزات (سطر لكل ميزة)</label><textarea name="features" rows="3">{{ implode("\n", $p->features ?? []) }}</textarea>
    <label class="check"><input type="checkbox" name="custom_domain" value="1" @checked($p->custom_domain)> دومين خاص</label>
    <label class="check"><input type="checkbox" name="is_active" value="1" @checked($p->is_active)> مفعلة</label>
    <button class="btn mt">حفظ</button>
</form>
@endforeach
</div>
@endsection
