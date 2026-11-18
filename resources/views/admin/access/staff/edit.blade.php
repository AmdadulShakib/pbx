@extends('admin.layouts.app')

@section('title', __('Edit Staff'))
@section('page-title', __('Edit Staff'))

@section('content')
    <form method="post" action="{{ route('admin.staff.update', $staff) }}" enctype="multipart/form-data" class="card">
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
                <div class="col-md-12">
                    <label class="form-label">@lang('Profile Photo / Avatar')</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        @if($staff->avatar_url)
                            <img id="avatarPreviewImg" src="{{ $staff->avatar_url }}" alt="{{ $staff->name }}" class="rounded-circle border" style="width: 56px; height: 56px; object-fit: cover;">
                        @else
                            <div id="avatarPlaceholder" class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border" style="width: 56px; height: 56px; font-size: 20px;">
                                {{ strtoupper(substr($staff->name, 0, 1)) }}
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
