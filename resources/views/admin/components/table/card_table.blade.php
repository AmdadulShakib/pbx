<div class="card table-card">
    <div class="card-header table-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="fw-semibold">{{ $title ?? '' }}</div>
            @if(!empty($subtitle))
                <div class="text-muted small">{{ $subtitle }}</div>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto">
            @if(!empty($search))
                <div class="table-search-input">
                    <i data-lucide="search"></i>
                    <input type="text" class="form-control form-control-sm table-search" data-table-search placeholder="{{ $searchPlaceholder ?? __('Quick search...') }}">
                </div>
            @endif
            {!! $actions ?? '' !!}
        </div>
    </div>
    @if(!empty($filters))
        {!! $filters !!}
    @endif
    <div class="card-body p-0">
        {!! $table ?? '' !!}
    </div>
    @if(!empty(trim($pagination ?? '')))
        <div class="card-footer">
            {!! $pagination !!}
        </div>
    @endif
</div>
