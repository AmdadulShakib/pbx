@php
    $label = $widget['label'] ?? __('Trend');
    $points = $widget['points'] ?? [];
    $labels = array_map(fn ($point) => $point['label'] ?? '', $points);
    $values = array_map(fn ($point) => (int) ($point['value'] ?? 0), $points);
@endphp

<div class="col-12 col-lg-6">
    <div class="card h-100 dashboard-chart-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">{{ $label }}</h5>
            @if(!empty($widget['range_label']))
                <span class="badge text-bg-light">{{ $widget['range_label'] }}</span>
            @endif
        </div>
        <div class="card-body">
            <div class="dashboard-chart-canvas js-apex-chart"
                 data-chart-type="line"
                 data-chart-height="220"
                 data-chart-series='@json([["name" => $label, "data" => $values]])'
                 data-chart-labels='@json($labels)'></div>
            @if(empty($points))
                <p class="text-muted mb-0">@lang('No data found')</p>
            @endif
        </div>
    </div>
</div>
