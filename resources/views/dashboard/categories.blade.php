@extends('layouts.dashboard')
@section('title', 'التصنيفات')
@section('content')
<div class="grid g2" style="align-items:start">
<form method="post" action="{{ route('dashboard.categories.store') }}" enctype="multipart/form-data" class="card">@csrf<h3>تصنيف جديد</h3><label>الاسم</label><input name="name" required><label>صورة</label><input type="file" name="image" accept="image/*"><button class="btn mt">إضافة</button></form>
<div class="table-wrap"><table><tr><th>التصنيف</th><th>المنتجات</th><th></th></tr>
@foreach ($categories as $c)<tr><td>{{ $c->name }}</td><td>{{ $c->products_count }}</td><td><form method="post" action="{{ route('dashboard.categories.destroy', $c) }}">@csrf @method('delete')<button class="btn sm danger">حذف</button></form></td></tr>@endforeach
</table></div></div>
@endsection
