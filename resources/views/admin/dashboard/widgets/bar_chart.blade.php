@php
    $label = $widget['label'] ?? __('Distribution');
    $items = $widget['items'] ?? [];
    $labels = array_map(fn ($item) => $item['label'] ?? '', $items);
    $values = array_map(fn ($item) => (int) ($item['value'] ?? 0), $items);
@endphp

<div class="col-12 col-lg-6">
    <div class="card h-100 dashboard-chart-card">
        <div class="card-header">
            <h5 class="mb-0">{{ $label }}</h5>
        </div>
        <div class="card-body">
            <div class="dashboard-chart-canvas js-apex-chart"
                 data-chart-type="bar"
                 data-chart-height="240"
                 data-chart-series='@json([["name" => $label, "data" => $values]])'
                 data-chart-labels='@json($labels)'></div>
            @if(empty($items))
                <p class="text-muted mb-0">@lang('No data found')</p>
            @endif
        </div>
    </div>
</div>
