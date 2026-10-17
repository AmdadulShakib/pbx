<div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <div class="fw-semibold">{{ $title ?? '' }}</div>
            @if(!empty($subtitle))
                <div class="text-muted small">{{ $subtitle }}</div>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!empty($search))
                <input type="text" class="form-control form-control-sm table-search" data-table-search placeholder="{{ $searchPlaceholder ?? __('Quick search...') }}">
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
        <div class="card-footer bg-white">
            {!! $pagination !!}
        </div>
    @endif
</div>
