@extends('admin.layouts.app')
@section('title', __('Business Settings'))
@section('page-title', __('Business Settings'))
@section('content')
    @include('admin.business_settings._tabs')

    <div class="card">
        <div class="card-body text-center text-muted">
            <i data-lucide="sparkles" class="mb-2"></i>
            <div class="fw-semibold">@lang('Coming soon')</div>
        </div>
    </div>
@endsection
