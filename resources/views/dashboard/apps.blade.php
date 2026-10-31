@extends('layouts.dashboard')
@section('title', 'متجر التطبيقات')
@section('content')
@foreach ($apps as $cat => $list)
<h3 class="mt">{{ ['payment'=>'الدفع','shipping'=>'الشحن','marketing'=>'التسويق','messaging'=>'التواصل'][$cat] ?? $cat }}</h3>
<div class="grid g3">@foreach ($list as $a)<div class="card"><h3>{{ $a->name }}</h3><p class="small muted">{{ $a->description }}</p><div class="row between"><span>{{ $a->price_monthly > 0 ? money($a->price_monthly).'/شهر' : 'مجاني' }}</span>
<form method="post" action="{{ route('dashboard.apps.toggle', $a) }}">@csrf<button class="btn sm {{ in_array($a->id, $enabled) ? 'ghost' : '' }}">{{ in_array($a->id, $enabled) ? 'إيقاف' : 'تثبيت' }}</button></form></div></div>@endforeach</div>
@endforeach
@endsection
