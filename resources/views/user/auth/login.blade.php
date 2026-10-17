@extends('layouts.site')

@section('title', 'User Login')
@section('body-class', 'auth-body')

@section('content')
<div class="auth-shell">
    <div class="auth-grid">
        <div class="auth-side">
            <div>
                <h1>Welcome back</h1>
                <p>Access your account to manage orders, track status, and keep your profile updated.</p>
                <div class="auth-list">
                    <div class="auth-list-item">Fast access to your data</div>
                    <div class="auth-list-item">Secure sessions and ownership rules</div>
                    <div class="auth-list-item">Clean, mobile-ready experience</div>
                </div>
            </div>
            <div>
                <a class="btn btn-ghost" href="{{ url('/') }}">Back to home</a>
            </div>
        </div>

        <div class="auth-card">
            <h2 class="auth-title">Sign in</h2>
            <p class="auth-sub">Use your email and password to continue.</p>

            @if (session('error'))
                <div class="notice notice-error">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="notice notice-success">{{ session('success') }}</div>
            @endif

            <form method="post" action="{{ route('login') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input class="input" type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" type="password" name="password" id="password" required>
                    <button type="button" class="password-toggle-btn" data-password-toggle data-target="password" aria-label="Toggle password">
                        <span class="icon-eye" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span class="icon-eye-off" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6 0-10-7-10-7a21.82 21.82 0 0 1 5.06-5.94"></path>
                                <path d="M1 1l22 22"></path>
                                <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c6 0 10 7 10 7a21.82 21.82 0 0 1-3.17 4.32"></path>
                                <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                            </svg>
                        </span>
                    </button>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="auth-actions">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                </div>
                <button class="btn btn-accent" type="submit">Sign in</button>
                <div class="auth-footer">
                    New here? <a href="{{ route('register') }}">Create an account</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
        var target = toggle.getAttribute('data-target');
        var input = target ? document.getElementById(target) : null;
        if (!input) return;
        toggle.addEventListener('click', function () {
            var isText = input.getAttribute('type') === 'text';
            input.setAttribute('type', isText ? 'password' : 'text');
            toggle.classList.toggle('is-on', !isText);
        });
    });
</script>
@endpush
