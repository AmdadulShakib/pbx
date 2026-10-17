@extends('admin.layouts.app')

@section('title', __('Create Role'))
@section('page-title', __('Create Role'))

@section('content')
    <form method="post" action="{{ route('admin.roles.store') }}" novalidate>
        @csrf
        <div class="card form-card mb-3">
            <div class="card-header form-card-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold">@lang('New Role')</div>
                        <div class="text-muted small mt-1">@lang('Create a new role and define its module access permissions.')</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @include('admin.access.roles._form')
            </div>
        </div>
        <div class="sticky-actions d-flex flex-wrap justify-content-end gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-times me-1"></i> @lang('Cancel')
            </a>
            <button class="btn btn-light" type="reset">
                <i class="fa fa-refresh me-1"></i> @lang('Reset')
            </button>
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-save me-1"></i> @lang('Save Role')
            </button>
        </div>
    </form>
@endsection
