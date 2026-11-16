@extends('layouts.site')

@section('title', 'Forgot Password')
@section('body-class', 'auth-body')

@section('content')
<div class="auth-shell">
    <div class="auth-grid">
        <div class="auth-side">
            <div>
                <a href="{{ url('/') }}" style="display: inline-block; margin-bottom: 28px;">
                    <img src="{{ asset('branding/logo-white.svg') }}" alt="VoxPulse PBX" height="34">
                </a>
                <h1>Reset access</h1>
                <p>Submit your email to recover your account and restore softphone access.</p>
                <div class="auth-list">
                    <div class="auth-list-item">Encrypted password reset token</div>
                    <div class="auth-list-item">Instant access restoration</div>
                    <div class="auth-list-item">Direct integration with PBX credentials</div>
                </div>
            </div>
            <div style="margin-top: 32px;">
                <a class="btn btn-secondary btn-sm" href="{{ route('login') }}">Back to login</a>
            </div>
        </div>

        <div class="auth-card">
            <h2 class="auth-title">Forgot password</h2>
            <p class="auth-sub">We will send a reset message if your email exists.</p>

            @if (session('error'))
                <div class="notice notice-error">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="notice notice-success">{{ session('success') }}</div>
            @endif

            <form method="post" action="{{ route('password.request') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input class="input" type="email" name="email" id="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <button class="btn btn-accent" type="submit">Send reset link</button>
                <div class="auth-footer">
                    Remembered? <a href="{{ route('login') }}">Sign in</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
