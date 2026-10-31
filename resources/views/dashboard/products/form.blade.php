@extends('layouts.dashboard')
@section('title', $product->exists ? 'تعديل منتج' : 'منتج جديد')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}" class="grid g2" style="align-items:start">
    @csrf @if ($product->exists) @method('put') @endif
    <div class="card">
        <label>اسم المنتج</label><input name="name" value="{{ old('name', $product->name) }}" required>
        <label>نوع المنتج</label>
        <select name="type" id="ptype">@foreach (['simple'=>'منتج بسيط','variable'=>'منتج بخيارات (مقاس/لون)','digital'=>'منتج رقمي (ملف/كود)'] as $k=>$l)<option value="{{ $k }}" @selected(old('type',$product->type)===$k)>{{ $l }}</option>@endforeach</select>
        <label>التصنيف</label><select name="category_id"><option value="">— بدون —</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->name }}</option>@endforeach</select>
        <label>الوصف</label><textarea name="description" rows="6">{{ old('description', $product->description) }}</textarea>
        <label>الصور</label><input type="file" name="images[]" accept="image/*" multiple>
        @if ($product->images)<div class="row mt">@foreach ($product->images as $img)<label class="small" style="text-align:center"><img src="{{ asset('storage/'.$img) }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px" alt=""><input type="checkbox" name="remove_images[]" value="{{ $img }}"> حذف</label>@endforeach</div>@endif
        <div id="digital-box"><label>الملف الرقمي</label><input type="file" name="digital"></div>
    </div>
    <div>
        <div class="card mb">
            <div class="grid g2">
                <div><label>السعر</label><input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required></div>
                <div><label>العملة</label><select name="price_currency"><option value="SDG">جنيه سوداني</option><option value="USD" @selected(old('price_currency',$product->price_currency)==='USD')>دولار (يحوّل تلقائياً بسعر الصرف)</option></select></div>
                <div><label>السعر قبل الخصم</label><input type="number" step="0.01" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}"></div>
                <div><label>التكلفة (للأرباح)</label><input type="number" step="0.01" name="cost" value="{{ old('cost', $product->cost) }}"></div>
                <div><label>SKU</label><input name="sku" value="{{ old('sku', $product->sku) }}"></div>
                <div><label>الوزن (كجم)</label><input type="number" step="0.001" name="weight_kg" value="{{ old('weight_kg', $product->weight_kg) }}"></div>
            </div>
            <label class="check mt"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> منشور في المتجر</label>
            <label class="check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> منتج مميز في الرئيسية</label>
            <label class="check"><input type="checkbox" name="track_stock" value="1" @checked(old('track_stock', $product->track_stock))> تتبع المخزون</label>
            <label>تنبيه عند وصول الكمية إلى</label><input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 3) }}">
        </div>
        <div class="card mb" id="simple-stock"><label>الكمية المتوفرة (الفرع الرئيسي)</label><input type="number" name="stock" value="{{ old('stock', $product->exists ? $product->stockLevels->whereNull('variant_id')->sum('quantity') : '') }}"></div>
        <div class="card mb" id="variants-box">
            <h3>الخيارات</h3><p class="small muted">اكتب الخيارات بالشكل: <code>المقاس:L، اللون:أحمر</code></p>
            @php($rows = $product->exists ? $product->variants : collect())
            @foreach ($rows as $i => $v)
                <div class="grid g3"><input name="variants[{{ $i }}][options]" value="{{ collect($v->options)->map(fn($x,$k)=>"$k:$x")->implode('، ') }}"><input name="variants[{{ $i }}][price]" value="{{ $v->price }}" placeholder="السعر (اختياري)"><input name="variants[{{ $i }}][stock]" value="{{ $product->stockLevels->where('variant_id',$v->id)->sum('quantity') }}" placeholder="الكمية"></div>
            @endforeach
            @for ($i = $rows->count(); $i < $rows->count() + 4; $i++)
                <div class="grid g3 mt"><input name="variants[{{ $i }}][options]" placeholder="المقاس:M، اللون:أسود"><input name="variants[{{ $i }}][price]" placeholder="السعر (اختياري)"><input name="variants[{{ $i }}][stock]" placeholder="الكمية"></div>
            @endfor
        </div>
        @if ($product->exists && $branches->count() > 1)
            <div class="card mb"><h3>المخزون حسب الفرع</h3>
                @foreach ($branches as $b)
                    <form method="post" action="{{ route('dashboard.products.stock', $product) }}" class="row">@csrf<input type="hidden" name="branch_id" value="{{ $b->id }}"><span style="min-width:110px">{{ $b->name }}</span><input type="number" name="quantity" value="{{ $product->stockLevels->where('branch_id',$b->id)->whereNull('variant_id')->sum('quantity') }}" style="width:100px"><button class="btn sm ghost">حفظ</button></form>
                @endforeach
            </div>
        @endif
        <button class="btn w100">حفظ المنتج</button>
    </div>
</form>
<script>const t=document.getElementById('ptype');function u(){document.getElementById('variants-box').hidden=t.value!=='variable';document.getElementById('simple-stock').hidden=t.value!=='simple';document.getElementById('digital-box').hidden=t.value!=='digital'}t.onchange=u;u();</script>
@endsection
