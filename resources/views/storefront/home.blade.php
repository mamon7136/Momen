@extends('layouts.store')
@section('content')
@foreach ($store->themeConfig()['sections'] as $section)
    @switch($section['type'])
        @case('hero')
            <section class="hero" @if (! empty($section['image'])) style="background-image:linear-gradient(rgba(0,0,0,.35),rgba(0,0,0,.35)),url('{{ asset('storage/'.$section['image']) }}')" @endif>
                <h1>{{ $section['title'] ?? $store->name }}</h1>
                <p>{{ $section['subtitle'] ?? '' }}</p>
                <a href="{{ $section['link'] ?? sroute('products') }}" class="btn sec">تسوق الآن</a>
            </section>
            @break
        @case('categories')
            @if ($categories->isNotEmpty())
                <div class="chips mb">@foreach ($categories as $c)<a class="chip" href="{{ sroute('category', ['slug' => $c->slug]) }}">{{ $c->name }}</a>@endforeach</div>
            @endif
            @break
        @case('featured_products')
            @if ($featured->isNotEmpty())
                <h2 class="mt">{{ $section['title'] ?? 'منتجات مميزة' }}</h2>
                <div class="grid g4">@foreach ($featured as $p)@include('partials.product-card')@endforeach</div>
            @endif
            @break
        @case('latest_products')
            <h2 class="mt">{{ $section['title'] ?? 'وصل حديثاً' }}</h2>
            <div class="grid g4">@forelse ($latest as $p)@include('partials.product-card')@empty<p class="muted">لا توجد منتجات بعد.</p>@endforelse</div>
            @break
        @case('banner')
            @if (! empty($section['image']))<a href="{{ $section['link'] ?? '#' }}" class="mt" style="display:block"><img src="{{ asset('storage/'.$section['image']) }}" alt="{{ $section['title'] ?? '' }}" style="border-radius:12px;width:100%" loading="lazy"></a>@endif
            @break
        @case('text')
            <div class="card mt"><h3>{{ $section['title'] ?? '' }}</h3><p>{!! nl2br(e($section['subtitle'] ?? '')) !!}</p></div>
            @break
        @case('whatsapp_cta')
            @if ($store->whatsapp)<div class="card mt center"><h3>{{ $section['title'] ?? 'محتاج مساعدة؟' }}</h3><a class="btn wa" href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}">💬 كلمنا واتساب</a></div>@endif
            @break
    @endswitch
@endforeach
@endsection
