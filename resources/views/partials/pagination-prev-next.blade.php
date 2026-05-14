@if($paginator->hasPages())
    <div class="sed-pagination" role="navigation" aria-label="{{ $ariaLabel ?? 'Пагинация' }}" style="margin-top:1rem;">
        <span>Стр. {{ $paginator->currentPage() }} из {{ $paginator->lastPage() }}</span>
        <div class="sed-pagination__pages">
            @if($paginator->onFirstPage())
                <span class="sed-page-link" aria-disabled="true">«</span>
            @else
                <a class="sed-page-link" href="{{ $paginator->previousPageUrl() }}">«</a>
            @endif
            <span class="sed-page-link sed-page-link--current">{{ $paginator->currentPage() }}</span>
            @if($paginator->hasMorePages())
                <a class="sed-page-link" href="{{ $paginator->nextPageUrl() }}">»</a>
            @else
                <span class="sed-page-link" aria-disabled="true">»</span>
            @endif
        </div>
    </div>
@endif
