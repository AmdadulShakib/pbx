@extends('admin.layouts.app')

@section('title', __('Edit User'))
@section('page-title', __('Edit User'))

@section('content')
    <form method="post" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="card">
        @csrf
        @method('put')
        <div class="card-header">
            <div class="fw-semibold">@lang('Update user')</div>
            <div class="text-muted small">@lang('Edit customer details.')</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">@lang('Full name')</label>
                    <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('Email')</label>
                    <input class="form-control" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">@lang('Profile Photo / Avatar')</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        @if($user->avatar_url)
                            <img id="avatarPreviewImg" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border" style="width: 56px; height: 56px; object-fit: cover;">
                        @else
                            <div id="avatarPlaceholder" class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border" style="width: 56px; height: 56px; font-size: 20px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <img id="avatarPreviewImg" src="" alt="Preview" class="rounded-circle border d-none" style="width: 56px; height: 56px; object-fit: cover;">
                        @endif
                        <div class="flex-grow-1">
                            <input class="form-control" name="avatar" type="file" accept="image/*" onchange="previewAvatar(this)">
                            <div class="form-text">@lang('Upload a new photo (JPG, PNG, WEBP, max 2MB). Leave empty to keep current.')</div>
                        </div>
                    </div>
                    @error('avatar')
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
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">@lang('Cancel')</a>
            <button class="btn btn-primary" type="submit">@lang('Update')</button>
        </div>
    </form>
@endsection

@push('js')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatarPreviewImg');
                img.src = e.target.result;
                img.classList.remove('d-none');
                const placeholder = document.getElementById('avatarPlaceholder');
                if (placeholder) placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
