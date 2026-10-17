@extends('layouts.site')

@section('title', 'Account Security')
@section('body-class', 'account-body')

@section('content')
<div class="account-shell">
    @include('user.partials.account_nav', ['user' => $user])

    <section class="account-hero">
        <div>
            <p class="eyebrow">Security</p>
            <h1 class="account-title">Protect your account</h1>
            <p class="account-sub">Update your password regularly and keep your access secure.</p>
        </div>
        <div class="hero-card highlight-card">
            <div class="card-meta">Last update</div>
            <div class="card-value">{{ $user?->updated_at?->diffForHumans() ?? 'Not available' }}</div>
            <p class="card-sub">Keeping your account secure helps prevent unauthorized access.</p>
        </div>
    </section>

    <div class="account-grid">
        <section class="account-card" style="--delay: 1;">
            <h2 class="card-title">Change password</h2>
            <p class="card-sub">Use a strong password with letters, numbers, and symbols.</p>

            @if (session('success'))
                <div class="notice notice-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="notice notice-error">{{ session('error') }}</div>
            @endif

            <form method="post" action="{{ route('user.security.update') }}">
                @csrf
                @method('put')

                <div class="field">
                    <label for="current_password">Current password</label>
                    <input class="input" id="current_password" type="password" name="current_password" required>
                    @error('current_password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">New password</label>
                    <input class="input" id="password" type="password" name="password" required>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
                </div>

                <div class="form-actions">
                    <button class="btn btn-accent" type="submit">Update password</button>
                </div>
            </form>
        </section>

        <aside class="account-card soft-card" style="--delay: 2;">
            <h3 class="card-title">Security checklist</h3>
            <ul class="checklist">
                <li>Use at least 8 characters</li>
                <li>Mix uppercase and lowercase letters</li>
                <li>Add numbers and special characters</li>
                <li>Avoid reusing old passwords</li>
            </ul>
            <div class="tip-card">
                <h4>Need help?</h4>
                <p>Contact support for account recovery and access questions.</p>
                <span class="chip">Support coming soon</span>
            </div>
        </aside>
    </div>
</div>
@endsection
