@extends('layouts.dashboard')
@section('title', 'متجر الثيمات')
@section('content')
<div class="tabs">
    <a href="{{ route('dashboard.themes.index') }}" class="on">متجر الثيمات</a>
    <a href="{{ route('dashboard.design.edit') }}">تخصيص التصميم</a>
    <a href="{{ route('dashboard.settings.edit') }}#domain">دومين المتجر</a>
</div>
<div class="hero-card">
    <div class="art">{{ icon('layout', 48) }}</div>
    <div style="flex:1">
        <span class="chip-s">متاحة في باقتك</span>
        <h2 style="margin:8px 0 4px">الثيم المناسب يبدأ من هنا</h2>
        <p class="muted" style="margin:0">كل ثيم له تصميم وتخطيط مختلف بالكامل: الهيدر، البطاقات، الصفحة الرئيسية والتذييل. جرّبه على متجرك قبل التطبيق، ثم خصّص الألوان والأقسام كما تريد.</p>
    </div>
    @if ($current)<div class="center"><div class="small muted">ثيمك الحالي</div><b style="font-size:1.2rem">{{ $current->name }}</b><div class="mt"><a href="{{ route('dashboard.design.edit') }}" class="btn dark sm">{{ icon('palette', 16) }} تخصيص</a></div></div>@endif
</div>
<form class="dt-search mb" style="max-width:none"><input name="q" value="{{ request('q') }}" placeholder="ابحث باسم الثيم أو التصنيف"><button>{{ icon('search', 18) }}</button></form>
<h3>اختر ثيم يلفت نظر عملائك {{ icon('sparkles', 18) }}</h3>
<div class="pills">
    <a href="{{ route('dashboard.themes.index') }}" class="{{ request('cat') ? '' : 'on' }}">{{ icon('star', 16) }} كل الثيمات</a>
    @foreach ($categories as $k => $l)<a href="{{ route('dashboard.themes.index', ['cat' => $k]) }}" class="{{ request('cat') === $k ? 'on' : '' }}">{{ $l }}</a>@endforeach
</div>
<div class="tgrid">
@forelse ($themes as $t)
    @php($s = $t->default_settings ?? [])
    @php($isCurrent = $current?->id === $t->id)
    <div class="tcard">
        <div class="shot" style="--tc:{{ data_get($s, 'colors.primary', '#0f6e6e') }}">
            @if ($isCurrent)<span class="cur">✓ الثيم الحالي</span>@endif
            <div class="desk">@if (file_exists(public_path('themes/'.$t->slug.'/preview.jpg')))<img src="{{ asset('themes/'.$t->slug.'/preview.jpg') }}" alt="{{ $t->name }}" loading="lazy">@endif</div>
            <div class="mob">@if (file_exists(public_path('themes/'.$t->slug.'/preview-m.jpg')))<img src="{{ asset('themes/'.$t->slug.'/preview-m.jpg') }}" alt="" loading="lazy">@endif</div>
        </div>
        <div class="info">
            <div class="r1"><span class="name">{{ $t->name }}</span><span class="swatches">@foreach (['primary','secondary','background','text'] as $c)<i style="background:{{ data_get($s, 'colors.'.$c) }}"></i>@endforeach</span></div>
            <p class="small muted" style="margin:0">{{ data_get($s, 'meta.description') }}</p>
            <div class="tags">@foreach (data_get($s, 'meta.tags', []) as $tag)<span class="chip-s">{{ $tag }}</span>@endforeach<span class="chip-s">{{ icon('mobile', 12) }} {{ icon('monitor', 12) }} متجاوب</span></div>
            <div class="row between"><b>{{ $t->price > 0 ? money($t->price) : 'مجاني' }}</b><span class="small muted">خط {{ data_get($s, 'font') }}</span></div>
            <div class="acts">
                <a href="{{ sroute_for($store, 'home') }}?preview_theme={{ $t->slug }}" target="_blank" class="btn ghost sm" style="flex:1">{{ icon('eye', 16) }} معاينة على متجري</a>
                @if ($isCurrent)
                    <a href="{{ route('dashboard.design.edit') }}" class="btn mint sm" style="flex:1">{{ icon('palette', 16) }} تخصيص</a>
                @else
                    <form method="post" action="{{ route('dashboard.design.theme') }}" style="flex:1" onsubmit="return confirm('تطبيق ثيم {{ $t->name }}؟ ستعود الألوان والأقسام لإعدادات الثيم الافتراضية.')">@csrf<input type="hidden" name="theme_id" value="{{ $t->id }}"><button class="btn dark sm w100">تطبيق الثيم</button></form>
                @endif
            </div>
        </div>
    </div>
@empty<div class="panel empty">لا توجد ثيمات مطابقة</div>@endforelse
</div>
@endsection
