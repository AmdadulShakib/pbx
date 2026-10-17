@php
    $rows = $widget['rows'] ?? collect();
    $label = $widget['label'] ?? __('Recent');
    $displayColumn = $widget['display_column'] ?? null;
    $dateColumn = $widget['date_column'] ?? null;
@endphp

<div class="col-12 col-md-6 col-lg-6">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">{{ __('Recent :label', ['label' => $label]) }}</h5>
            @if(!empty($widget['route']))
                <a href="{{ route($widget['route']) }}" class="btn btn-sm btn-outline-secondary">@lang('View all')</a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('ID')</th>
                            @if($displayColumn)
                                <th>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $displayColumn)) }}</th>
                            @endif
                            @if($dateColumn)
                                <th>@lang('Created')</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->id ?? '-' }}</td>
                                @if($displayColumn)
                                    <td>{{ $row->{$displayColumn} ?? '-' }}</td>
                                @endif
                                @if($dateColumn)
                                    <td>{{ $row->{$dateColumn} ?? '-' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">@lang('No data found')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
