@php
    $perPageOptions = [10, 20, 50, 100];
    $currentPerPage = (int) request('per_page', $paginator->perPage());
    if (!in_array($currentPerPage, $perPageOptions, true)) {
        $currentPerPage = $paginator->perPage();
    }
    $query = request()->except(['page', 'per_page']);
    $totalCount = method_exists($paginator, 'total') ? $paginator->total() : null;
    $from = method_exists($paginator, 'firstItem') ? $paginator->firstItem() : null;
    $to = method_exists($paginator, 'lastItem') ? $paginator->lastItem() : null;
    $showTotals = $totalCount !== null;
    $canGoto = method_exists($paginator, 'lastPage');
@endphp

@if($paginator->hasPages())
<div class="pagination-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="text-muted small">
        @if($showTotals)
            {{ (int) ($from ?? 0) }}-{{ (int) ($to ?? 0) }} of {{ (int) $totalCount }}
        @endif
    </div>
    <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
        <nav aria-label="Pagination">
            <ul class="pagination mb-0">
                @if($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" aria-hidden="true">
                            <i data-lucide="chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                            <i data-lucide="chevron-left"></i>
                        </a>
                    </li>
                @endif

                @foreach($elements as $element)
                    @if(is_string($element))
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif
                    @if(is_array($element))
                        @foreach($element as $page => $url)
                            @if($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                            <i data-lucide="chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" aria-hidden="true">
                            <i data-lucide="chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
        <form method="get" class="d-flex flex-wrap align-items-center gap-2 pagination-controls">
            @foreach($query as $key => $value)
                @if(is_array($value))
                    @foreach($value as $item)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <div class="d-flex align-items-center gap-2">
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($currentPerPage == $option)>{{ $option }} / page</option>
                    @endforeach
                </select>
            </div>
            @if($canGoto)
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Go to</span>
                    <input type="number" name="page" min="1" max="{{ $paginator->lastPage() }}" value="{{ $paginator->currentPage() }}" class="form-control form-control-sm page-input">
                    <button class="btn btn-outline-secondary btn-sm" type="submit">Go</button>
                </div>
            @endif
        </form>
    </div>
</div>
@endif
