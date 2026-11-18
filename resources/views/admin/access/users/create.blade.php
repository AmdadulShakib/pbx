@extends('admin.layouts.app')

@section('title', __('Add User'))
@section('page-title', __('Add User'))

@section('content')
    <form method="post" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="card">
        @csrf
        <div class="card-header">
            <div class="fw-semibold">@lang('Create user')</div>
            <div class="text-muted small">@lang('Add a new customer account.')</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">@lang('Full name')</label>
                    <input class="form-control" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('Email')</label>
                    <input class="form-control" name="email" type="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">@lang('Profile Photo / Avatar')</label>
                    <input class="form-control" name="avatar" type="file" accept="image/*" onchange="previewAvatar(this)">
                    <div class="form-text">@lang('Accepted formats: JPG, PNG, WEBP. Max size: 2MB.')</div>
                    <div id="avatarPreviewBox" class="mt-2 d-none">
                        <img id="avatarPreviewImg" src="" alt="Preview" class="rounded-circle border" style="width: 64px; height: 64px; object-fit: cover;">
                    </div>
                    @error('avatar')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('Password')</label>
                    <input class="form-control" name="password" type="password" required>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('Confirm password')</label>
                    <input class="form-control" name="password_confirmation" type="password" required>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">@lang('Cancel')</a>
            <button class="btn btn-primary" type="submit">@lang('Save')</button>
        </div>
    </form>
@endsection

@push('js')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreviewImg').src = e.target.result;
                document.getElementById('avatarPreviewBox').classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
