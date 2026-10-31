@extends('layouts.store')
@section('title', $title)
@section('content')
<div class="row between mb"><h1>{{ $title }}</h1>
    <form><input type="hidden" name="q" value="{{ request('q') }}"><select name="sort" onchange="this.form.submit()" style="min-height:38px"><option value="">الأحدث</option><option value="price_asc" @selected(request('sort')==='price_asc')>الأقل سعراً</option></select></form>
</div>
<div class="grid g4">@forelse ($products as $p)@include('partials.product-card')@empty<p class="muted">لا توجد منتجات.</p>@endforelse</div>
{{ $products->links('partials.pagination') }}
@endsection
