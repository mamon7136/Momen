@extends('layouts.dashboard')
@section('title', 'تصميم المتجر')
@section('content')
<div class="tabs">
    <a href="{{ route('dashboard.themes.index') }}">متجر الثيمات</a>
    <a href="{{ route('dashboard.design.edit') }}" class="on">تخصيص التصميم</a>
    <a href="{{ route('dashboard.settings.edit') }}#domain">دومين المتجر</a>
</div>
<div class="banner mint">{{ icon('layout', 22) }}<div class="grow">الثيم الحالي: <b>{{ $store->theme?->name }}</b> — غيّر الألوان والخط والأقسام أدناه، أو <a href="{{ route('dashboard.themes.index') }}">اختر ثيماً آخر</a>.</div><a href="{{ $store->primaryUrl() }}" target="_blank" class="btn sm ghost">{{ icon('eye', 16) }} معاينة</a></div>
<form method="post" action="{{ route('dashboard.design.update') }}" enctype="multipart/form-data" class="grid g2" style="align-items:start">@csrf
    <div class="card">
        <h3>الهوية</h3>
        <label>الشعار</label><input type="file" name="logo" accept="image/*">
        <div class="grid g2">@foreach (['primary'=>'اللون الأساسي','secondary'=>'اللون الثانوي','background'=>'الخلفية','text'=>'النص'] as $k=>$l)<div><label>{{ $l }}</label><input type="color" name="colors[{{ $k }}]" value="{{ $config['colors'][$k] ?? '#000000' }}"></div>@endforeach</div>
        <label>الخط</label><select name="font">@foreach (['Tajawal','Cairo','Almarai','IBM Plex Sans Arabic','Amiri','Noto Kufi Arabic','system'] as $f)<option @selected(($config['font'] ?? '')===$f)>{{ $f }}</option>@endforeach</select>
        <label>شريط الإعلانات</label><input name="announcement" value="{{ $config['announcement'] }}" placeholder="🚚 توصيل مجاني داخل الخرطوم للطلبات فوق 50,000">
        <label>نص التذييل</label><textarea name="footer_text" rows="2">{{ $config['footer_text'] }}</textarea>
    </div>
    <div class="card">
        <h3>أقسام الصفحة الرئيسية</h3><p class="small muted">رتّب الأقسام بتغيير الأرقام، واترك النوع "حذف" لإزالة قسم.</p>
        <div id="sections">
        @foreach (array_merge($config['sections'], [['type'=>'']]) as $i => $s)
            <div class="card mb" data-sec>
                <div class="row"><select name="sections[{{ $i }}][type]" style="flex:1" @if ($s['type']==='') data-new @endif><option value="">— حذف / بدون —</option>@foreach ($types as $k=>$l)<option value="{{ $k }}" @selected($s['type']===$k)>{{ $l }}</option>@endforeach</select>
                    <button type="button" class="btn ghost sm" onclick="mv(this,-1)">▲</button><button type="button" class="btn ghost sm" onclick="mv(this,1)">▼</button></div>
                <input name="sections[{{ $i }}][title]" value="{{ $s['title'] ?? '' }}" placeholder="العنوان" class="mt">
                <input name="sections[{{ $i }}][subtitle]" value="{{ $s['subtitle'] ?? '' }}" placeholder="النص الفرعي" class="mt">
                <input name="sections[{{ $i }}][link]" value="{{ $s['link'] ?? '' }}" placeholder="الرابط (اختياري)" class="mt">
                <input type="hidden" name="sections[{{ $i }}][image]" value="{{ $s['image'] ?? '' }}">
                <label class="small">صورة (للبانر/الإعلان)</label><input type="file" name="section_images[{{ $i }}]" accept="image/*">
            </div>
        @endforeach
        </div>
        <button class="btn w100">حفظ التصميم</button>
        <a href="{{ $store->primaryUrl() }}" target="_blank" class="btn ghost w100 mt">معاينة المتجر ↗</a>
    </div>
</form>
<script>
function mv(b,d){const c=b.closest('[data-sec]'),p=c.parentNode;d<0?c.previousElementSibling&&p.insertBefore(c,c.previousElementSibling):c.nextElementSibling&&p.insertBefore(c.nextElementSibling,c);reindex()}
function reindex(){document.querySelectorAll('[data-sec]').forEach((c,i)=>c.querySelectorAll('[name]').forEach(e=>e.name=e.name.replace(/\[\d+\]/,'['+i+']')))}
document.querySelector('form[enctype]').addEventListener('submit',()=>{document.querySelectorAll('[data-sec]').forEach(c=>{if(!c.querySelector('select').value)c.remove()});reindex()});
</script>
@endsection
