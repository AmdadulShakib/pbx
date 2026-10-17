@php
    $currentUser = $user ?? auth()->user();
@endphp

<header class="account-nav">
    <a class="brand" href="{{ url('/') }}">
        <span class="brand-mark">{{ strtoupper(substr(config('app.name', 'App'), 0, 1)) }}</span>
        <span>{{ config('app.name', 'App') }}</span>
    </a>
    <nav class="account-links">
        <a class="account-link {{ request()->routeIs('user.home') || request()->routeIs('user.dashboard') ? 'is-active' : '' }}"
           href="{{ route('user.dashboard') }}">Dashboard</a>
        <a class="account-link {{ request()->routeIs('user.profile') ? 'is-active' : '' }}"
           href="{{ route('user.profile') }}">Profile</a>
        <a class="account-link {{ request()->routeIs('user.security') ? 'is-active' : '' }}"
           href="{{ route('user.security') }}">Security</a>
    </nav>
    <div class="account-actions">
        <div class="account-user">
            <span class="account-name">{{ $currentUser?->name ?? 'User' }}</span>
            <span class="account-email">{{ $currentUser?->email ?? '' }}</span>
        </div>
        <form method="post" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost">Logout</button>
        </form>
    </div>
</header>
