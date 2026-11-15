@extends('admin.layouts.app')

@php
    $isEdit = $extension->exists;
    $title = $isEdit ? __('Edit Extension') . ' ' . $extension->extension : __('Add New Extension');
@endphp

@section('title', $title)
@section('page-title', $title)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-bold mb-1"><i data-lucide="alert-circle" class="me-2"></i>{{ __('Please correct the errors below:') }}</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0 fw-bold">
                        <i data-lucide="{{ $isEdit ? 'edit' : 'plus-circle' }}" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
                        {{ $title }}
                    </h5>
                    <small class="text-muted">{{ __('Configure SIP credentials and employee mapping') }}</small>
                </div>
                <a href="{{ route('admin.pbx.extensions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i data-lucide="arrow-left" class="me-1" style="width: 14px; height: 14px;"></i>
                    {{ __('Back to List') }}
                </a>
            </div>

            <form action="{{ $isEdit ? route('admin.pbx.extensions.update', $extension) : route('admin.pbx.extensions.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="extension" class="form-label fw-semibold">
                                {{ __('Extension Number') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('extension') is-invalid @enderror" 
                                   id="extension" 
                                   name="extension" 
                                   value="{{ old('extension', $extension->extension) }}" 
                                   placeholder="e.g. 101, 1003, 2001" 
                                   required 
                                   pattern="[0-9]{2,8}"
                                   maxlength="8">
                            <small class="text-muted">{{ __('Numeric extension (e.g. 101, 1003, 2001).') }}</small>
                            @error('extension')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label fw-semibold">
                                {{ __('Display Name / Caller ID') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $extension->name) }}" 
                                   placeholder="e.g. Support Desk, John Doe" 
                                   required 
                                   maxlength="50">
                            <small class="text-muted">{{ __('Name displayed on caller screen.') }}</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label fw-semibold">
                                {{ __('SIP Secret / Password') }} {!! $isEdit ? '<small class="text-muted fw-normal">(' . __('Leave blank to keep current') . ')</small>' : '<span class="text-danger">*</span>' !!}
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="{{ $isEdit ? '••••••••' : __('Minimum 8 characters') }}" 
                                       {{ $isEdit ? '' : 'required' }} 
                                       minlength="8" 
                                       maxlength="64">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility()">
                                    <i data-lucide="eye" id="eyeIcon" style="width: 16px; height: 16px;"></i>
                                </button>
                                <button class="btn btn-outline-primary" type="button" onclick="generateRandomPass()">
                                    {{ __('Generate') }}
                                </button>
                            </div>
                            <small class="text-muted">{{ __('Used by WebRTC client for SIP registration.') }}</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="user_id" class="form-label fw-semibold">
                                {{ __('Assign to Employee / User') }}
                            </label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                <option value="">-- {{ __('Select an Employee (Optional)') }} --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $extension->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('This employee will automatically connect to this extension upon login.') }}</small>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="enabled" 
                                       name="enabled" 
                                       value="1" 
                                       {{ old('enabled', $extension->enabled ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="enabled">
                                    {{ __('Extension Enabled') }}
                                </label>
                            </div>
                            <small class="text-muted">{{ __('Disabled extensions cannot register or receive calls.') }}</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light py-3 px-4 d-flex justify-content-end gap-2 border-top">
                    <a href="{{ route('admin.pbx.extensions.index') }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i data-lucide="check" class="me-1" style="width: 16px; height: 16px;"></i>
                        {{ $isEdit ? __('Update Extension') : __('Create Extension') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function togglePassVisibility() {
        const pass = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pass.type === 'password') {
            pass.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            pass.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function generateRandomPass() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let pass = '';
        for (let i = 0; i < 12; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const input = document.getElementById('password');
        input.type = 'text';
        input.value = pass;
        const icon = document.getElementById('eyeIcon');
        icon.setAttribute('data-lucide', 'eye-off');
        if (window.lucide) {
            lucide.createIcons();
        }
    }
</script>
@endpush
