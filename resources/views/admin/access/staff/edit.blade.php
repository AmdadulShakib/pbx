@extends('admin.layouts.app')

@section('title', __('Edit Staff'))
@section('page-title', __('Edit Staff'))

@section('content')
    <form method="post" action="{{ route('admin.staff.update', $staff) }}" class="card">
        @csrf
        @method('put')
        <div class="card-header">
            <div class="fw-semibold">@lang('Update staff account')</div>
            <div class="text-muted small">@lang('Change details or update assigned roles.')</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">@lang('Full name')</label>
                    <input class="form-control" name="name" value="{{ old('name', $staff->name) }}" required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('Email')</label>
                    <input class="form-control" name="email" type="email" value="{{ old('email', $staff->email) }}" required>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('New password')</label>
                    <input class="form-control" name="password" type="password">
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('Confirm password')</label>
                    <input class="form-control" name="password_confirmation" type="password">
                </div>
            </div>

            <div class="mt-4">
                <h6 class="fw-semibold">@lang('Assigned roles')</h6>
                <div class="row g-2">
                    @forelse($roles as $role)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="staff-role-{{ $role->id }}" name="roles[]" value="{{ $role->id }}"
                                       {{ in_array($role->id, old('roles', $assigned ?? []), true) ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="staff-role-{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <span class="text-muted">@lang('No roles available.')</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-light">@lang('Cancel')</a>
            <button class="btn btn-primary" type="submit">@lang('Update')</button>
        </div>
    </form>
@endsection
