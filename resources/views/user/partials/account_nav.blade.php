@php
    $currentUser = $user ?? auth()->user();
@endphp

<header class="account-nav">
    <a class="brand" href="{{ url('/') }}">
        <img src="{{ asset('branding/logo.svg') }}" alt="VoxPulse PBX" height="34">
    </a>
    <nav class="account-links">
        <a class="account-link {{ request()->routeIs('user.home') || request()->routeIs('user.dashboard') ? 'is-active' : '' }}"
           href="{{ route('user.dashboard') }}">Dashboard</a>
        <a class="account-link {{ request()->routeIs('user.pbx.phone') ? 'is-active' : '' }} d-inline-flex align-items-center gap-1"
           href="{{ route('user.pbx.phone') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <span>My Extension</span>
        </a>
        <a class="account-link {{ request()->routeIs('user.pbx.history') ? 'is-active' : '' }} d-inline-flex align-items-center gap-1"
           href="{{ route('user.pbx.history') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>Call History</span>
        </a>
        <a class="account-link {{ request()->routeIs('user.profile') ? 'is-active' : '' }}"
           href="{{ route('user.profile') }}">Profile</a>
        <a class="account-link {{ request()->routeIs('user.security') ? 'is-active' : '' }}"
           href="{{ route('user.security') }}">Security</a>
    </nav>
    <div class="account-actions">
        <button type="button" class="btn btn-secondary btn-sm" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();" title="Open Web Softphone">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <span>Phone Dialer</span>
        </button>
        <div class="account-user">
            <span class="account-name">{{ $currentUser?->name ?? 'User' }}</span>
            <span class="account-email">{{ $currentUser?->email ?? '' }}</span>
        </div>
        <form method="post" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
        </form>
    </div>
</header>
