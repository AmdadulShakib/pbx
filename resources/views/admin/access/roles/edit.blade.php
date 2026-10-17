@extends('admin.layouts.app')

@section('title', __('Edit Role'))
@section('page-title', __('Edit Role - ' . $role->name))

@section('content')
    <form method="post" action="{{ route('admin.roles.update', $role) }}" novalidate>
        @csrf
        @method('put')
        <div class="card form-card mb-3">
            <div class="card-header form-card-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold">@lang('Edit Role')</div>
                        <div class="text-muted small mt-1">@lang('Update role details and permissions.')</div>
                    </div>
                    @if($role->is_super)
                        <span class="badge badge-soft badge-soft-success">@lang('Super Admin')</span>
                    @else
                        <span class="badge badge-soft">@lang('Custom Role')</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @include('admin.access.roles._form')
            </div>
        </div>
        <div class="sticky-actions d-flex flex-wrap justify-content-end gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> @lang('Back')
            </a>
            <button class="btn btn-light" type="reset">
                <i class="fa fa-refresh me-1"></i> @lang('Reset')
            </button>
            <button class="btn btn-primary" type="submit" @if($role->is_super) disabled @endif>
                <i class="fa fa-save me-1"></i> @lang('Update Role')
            </button>
        </div>
    </form>
@endsection
