@if ($paginator->hasPages())
    <ul class="pagination pagination-sm justify-content-end">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item prev disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <a class="page-link waves-effect" href="javascript:void(0);">
                    <i class="ti ti-chevrons-left ti-xs"></i>
                </a>
            </li>
        @else
            <li class="page-item prev">
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"
                    class="page-link waves-effect">
                    <i class="ti ti-chevrons-left ti-xs"></i>
                </a>
            </li>
        @endif

        {{-- Current Page --}}
        <li class="page-item active">
            <a class="page-link waves-effect">{{ $paginator->currentPage() }}</a>
        </li>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item next">
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"
                    class="page-link waves-effect">
                    <i class="ti ti-chevrons-right ti-xs"></i>
                </a>
            </li>
        @else
            <li class="page-item next disabled">
                <a class="page-link waves-effect" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <i class="ti ti-chevrons-right ti-xs"></i>
                </a>
            </li>
        @endif
    </ul>
@endif
