@php
    $rows = $widget['rows'] ?? collect();
    $label = $widget['label'] ?? __('Recent');
    $columns = $widget['columns'] ?? [];
@endphp

<div class="col-12">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">{{ $label }}</h5>
            @if(!empty($widget['route']))
                <a href="{{ route($widget['route']) }}" class="btn btn-sm btn-outline-secondary">@lang('View all')</a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $column)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                @foreach($columns as $column)
                                    <td>{{ $row->{$column} ?? '-' }}</td>
                                @endforeach
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
