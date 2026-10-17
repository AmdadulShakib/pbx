@php
    $rows = $widget['rows'] ?? [];
    $label = $widget['label'] ?? __('Status Summary');
    $field = $widget['field'] ?? 'status';
@endphp

<div class="col-12 col-lg-6">
    <div class="card h-100">
        <div class="card-header">
            <h5 class="mb-0">{{ __(':label status summary', ['label' => $label]) }}</h5>
        </div>
        <div class="card-body">
            @forelse($rows as $row)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-secondary">{{ ucwords((string) ($row->{$field} ?? '')) }}</span>
                    <span class="fw-semibold">{{ (int) ($row->count ?? 0) }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">@lang('No data found')</p>
            @endforelse
        </div>
    </div>
</div>
