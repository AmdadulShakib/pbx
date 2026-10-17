@extends('admin.layouts.app')
@section('title', __('Access denied'))
@section('page-title', __('Access denied'))
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="fw-semibold mb-2">@lang('No access assigned')</div>
            <p class="text-muted mb-4">
                @lang('Your account does not have any admin permissions yet. Please contact an administrator to assign a role.')
            </p>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary">@lang('Sign out')</button>
            </form>
        </div>
    </div>
@endsection
