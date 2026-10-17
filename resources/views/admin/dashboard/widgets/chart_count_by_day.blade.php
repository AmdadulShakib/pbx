@php
    $points = $widget['points'] ?? [];
    $label = $widget['label'] ?? __('Activity');
    $labels = array_map(fn ($point) => $point['day'] ?? '', $points);
    $values = array_map(fn ($point) => (int) ($point['count'] ?? 0), $points);
@endphp

<div class="col-12 col-lg-6">
    <div class="card h-100 dashboard-chart-card">
        <div class="card-header">
            <h5 class="mb-0">{{ __(':label activity', ['label' => $label]) }}</h5>
        </div>
        <div class="card-body">
            <div class="dashboard-chart-canvas js-apex-chart"
                 data-chart-type="bar"
                 data-chart-height="220"
                 data-chart-series='@json([["name" => "Count", "data" => $values]])'
                 data-chart-labels='@json($labels)'></div>
            @if(empty($points))
                <p class="text-muted mb-0">@lang('No data found')</p>
            @endif
        </div>
    </div>
</div>
