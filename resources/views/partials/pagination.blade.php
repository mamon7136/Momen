@if ($paginator->hasPages())
<ul class="pagination">
    @if ($paginator->onFirstPage())<li><span class="muted">السابق</span></li>@else<li><a href="{{ $paginator->previousPageUrl() }}">السابق</a></li>@endif
    <li><span>{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span></li>
    @if ($paginator->hasMorePages())<li><a href="{{ $paginator->nextPageUrl() }}">التالي</a></li>@else<li><span class="muted">التالي</span></li>@endif
</ul>
@endif
