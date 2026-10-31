@if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
@if (session('error'))<div class="alert err">{{ session('error') }}</div>@endif
@if ($errors->any())
    <div class="alert err">@foreach ($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
@endif
