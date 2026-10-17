@php
    $label = $widget['label'] ?? __('Comparison');
    $current = $widget['current'] ?? 0;
    $previous = $widget['previous'] ?? 0;
    $delta = $widget['delta'] ?? null;
    $trend = $widget['trend'] ?? null;
@endphp

<div class="col-12 col-md-6">
    <div class="card h-100 dashboard-kpi">
        <div class="card-body">
            <div class="text-muted small">{{ $label }}</div>
            <div class="d-flex align-items-center gap-3">
                <div>
                    <div class="h4 mb-0">{{ number_format((int) $current) }}</div>
                    <div class="text-muted small">@lang('Current')</div>
                </div>
                <div>
                    <div class="h6 mb-0">{{ number_format((int) $previous) }}</div>
                    <div class="text-muted small">@lang('Previous')</div>
                </div>
                @if($delta !== null)
                    <span class="badge ms-auto {{ $trend === 'down' ? 'text-bg-danger' : ($trend === 'up' ? 'text-bg-success' : 'text-bg-secondary') }}">
                        {{ $delta }}%
                    </span>
                @endif
            </div>
            @if(!empty($widget['hint']))
                <div class="small text-muted mt-2">{{ $widget['hint'] }}</div>
            @endif
        </div>
    </div>
</div>
