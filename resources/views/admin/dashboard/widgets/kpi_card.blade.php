@php
    $label = $widget['label'] ?? __('Metric');
    $value = $widget['value'] ?? 0;
    $suffix = $widget['suffix'] ?? '';
    $delta = $widget['delta'] ?? null;
    $trend = $widget['trend'] ?? null;
@endphp

<div class="col-12 col-md-6 col-xl-4">
    <div class="card h-100 dashboard-kpi">
        <div class="card-body">
            <div class="text-muted small">{{ $label }}</div>
            <div class="d-flex align-items-end justify-content-between gap-2">
                <div class="h3 mb-0">{{ is_numeric($value) ? number_format((float) $value, 0) : $value }}{{ $suffix }}</div>
                @if($delta !== null)
                    <span class="badge {{ $trend === 'down' ? 'text-bg-danger' : ($trend === 'up' ? 'text-bg-success' : 'text-bg-secondary') }}">
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
