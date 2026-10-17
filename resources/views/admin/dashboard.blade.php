@extends('admin.layouts.app')
@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))
@section('content')
    <div class="row g-3">
        @forelse($widgets as $widget)
            @includeIf('admin.dashboard.widgets.' . ($widget['type'] ?? ''), ['widget' => $widget])
        @empty
            <div class="col-12">
                @include('admin.components.empty_state', [
                    'message' => __('No dashboard widgets'),
                    'helper' => __('Add widgets in schema config to populate the dashboard.'),
                ])
            </div>
        @endforelse
    </div>
@endsection
