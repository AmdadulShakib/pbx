@php
    $items = $widget['items'] ?? [];
@endphp

@foreach($items as $item)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body position-relative">
                <div class="text-muted small">{{ $item['label'] ?? __('Module') }}</div>
                <div class="h3 mb-0">{{ number_format((int) ($item['count'] ?? 0)) }}</div>
                @if(!empty($item['route']))
                    <a href="{{ route($item['route']) }}" class="stretched-link"></a>
                @endif
            </div>
        </div>
    </div>
@endforeach
