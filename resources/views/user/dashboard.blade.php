@extends('layouts.site')

@section('title', 'User Dashboard')
@section('body-class', 'account-body')

@section('content')
<div class="account-shell">
    @include('user.partials.account_nav', ['user' => $user])

    <section class="account-hero">
        <div>
            <p class="eyebrow">Dashboard</p>
            <h1 class="account-title">Welcome back, {{ $user?->name ?? 'User' }}</h1>
            <p class="account-sub">Manage your profile, security, and account insights in one place.</p>
            <div class="hero-actions">
                <a class="btn btn-accent" href="{{ route('user.profile') }}">Update profile</a>
                <a class="btn btn-ghost" href="{{ route('user.security') }}">Change password</a>
            </div>
        </div>
        <div class="hero-card highlight-card">
            <div class="card-meta">Profile completion</div>
            <div class="card-value">{{ $completion ?? 0 }}%</div>
            <div class="progress">
                <span class="progress-fill" style="width: {{ $completion ?? 0 }}%"></span>
            </div>
            <p class="card-sub">Finish your profile to unlock more personalized experiences.</p>
        </div>
    </section>

    <div class="dashboard-grid">
        <div class="account-card" style="--delay: 1;">
            <div class="card-icon">P</div>
            <h3 class="card-title">Profile</h3>
            <p class="card-sub">Update your personal details and contact information.</p>
            <a class="btn btn-ghost" href="{{ route('user.profile') }}">Edit profile</a>
        </div>
        <div class="account-card" style="--delay: 2;">
            <div class="card-icon">S</div>
            <h3 class="card-title">Security</h3>
            <p class="card-sub">Manage your password and keep your account secure.</p>
            <a class="btn btn-ghost" href="{{ route('user.security') }}">Security settings</a>
        </div>
        <div class="account-card" style="--delay: 3;">
            <div class="card-icon">O</div>
            <h3 class="card-title">Orders</h3>
            <p class="card-sub">Track purchases and view order history.</p>
            <span class="chip">Coming soon</span>
        </div>
        <div class="account-card" style="--delay: 4;">
            <div class="card-icon">H</div>
            <h3 class="card-title">Support</h3>
            <p class="card-sub">Need help? Reach out to support anytime.</p>
            <span class="chip">Coming soon</span>
        </div>
    </div>

    <div class="account-grid">
        <section class="account-card wide" style="--delay: 5;">
            <h3 class="card-title">Account insights</h3>
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
                    <span class="stat-label">Profile score</span>
                    <span class="stat-value">{{ $completion ?? 0 }}%</span>
                </div>
            </div>
        </section>
        <aside class="account-card soft-card" style="--delay: 6;">
            <h3 class="card-title">Recent activity</h3>
            <p class="card-sub">Activity updates will appear here once enabled.</p>
            <div class="empty-state">No activity yet.</div>
        </aside>
    </div>
</div>
@endsection
