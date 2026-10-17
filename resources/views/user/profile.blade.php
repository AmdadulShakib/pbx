@extends('layouts.site')

@section('title', 'My Profile')
@section('body-class', 'account-body')

@section('content')
<div class="account-shell">
    @include('user.partials.account_nav', ['user' => $user])

    <section class="account-hero">
        <div>
            <p class="eyebrow">Profile</p>
            <h1 class="account-title">Keep your details fresh</h1>
            <p class="account-sub">Update your information to keep your account accurate and secure.</p>
        </div>
        <div class="hero-card">
            <div class="card-meta">Profile completion</div>
            <div class="card-value">{{ $meta['completion'] ?? 0 }}%</div>
            <div class="progress">
                <span class="progress-fill" style="width: {{ $meta['completion'] ?? 0 }}%"></span>
            </div>
            <p class="card-sub">Add missing details to complete your profile.</p>
        </div>
    </section>

    <div class="account-grid">
        <section class="account-card wide" style="--delay: 1;">
            <h2 class="card-title">Profile information</h2>
            <p class="card-sub">Edit your account details and save changes.</p>

            @if (session('success'))
                <div class="notice notice-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="notice notice-error">{{ session('error') }}</div>
            @endif

            <form method="post" action="{{ route('user.profile.update') }}">
                @csrf
                @method('put')

                <div class="form-grid">
                    @if ($fields['name'] ?? false)
                        <div class="field">
                            <label for="name">Full name</label>
                            <input class="input" id="name" name="name" value="{{ old('name', $user?->name) }}" required>
                            @error('name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($fields['email'] ?? false)
                        <div class="field">
                            <label for="email">Email</label>
                            <input class="input" id="email" name="email" value="{{ old('email', $user?->email) }}" required>
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($fields['phone'] ?? false)
                        <div class="field">
                            <label for="phone">Phone</label>
                            <input class="input" id="phone" name="phone" value="{{ old('phone', $user?->phone) }}">
                            @error('phone')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($fields['address'] ?? false)
                        <div class="field">
                            <label for="address">Address</label>
                            <input class="input" id="address" name="address" value="{{ old('address', $user?->address) }}">
                            @error('address')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($fields['avatar'] ?? false)
                        <div class="field">
                            <label for="avatar">Avatar URL</label>
                            <input class="input" id="avatar" name="avatar" value="{{ old('avatar', $user?->avatar) }}">
                            @error('avatar')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <button class="btn btn-accent" type="submit">Save changes</button>
                    <a class="btn btn-ghost" href="{{ route('user.security') }}">Go to security</a>
                </div>
            </form>
        </section>

        <aside class="account-card soft-card" style="--delay: 2;">
            <h3 class="card-title">Account status</h3>
            <div class="stat-grid">
                <div class="stat-card">
                    <span class="stat-label">Member since</span>
                    <span class="stat-value">{{ $meta['member_since'] ?? 'Not available' }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Last updated</span>
                    <span class="stat-value">{{ $meta['last_updated'] ?? 'Not available' }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Email verified</span>
                    <span class="stat-value">{{ ($meta['email_verified'] ?? false) ? 'Yes' : 'Not yet' }}</span>
                </div>
            </div>
            <div class="tip-card">
                <h4>Quick tip</h4>
                <p>Complete your profile to unlock personalized recommendations.</p>
            </div>
        </aside>
    </div>
</div>
@endsection
