@php
    $ext = $user->pbxExtension ?? null;
@endphp

@extends('layouts.site')

@section('title', 'Employee Dashboard - VoxPulse PBX')
@section('body-class', 'account-body')

@section('content')
<div class="account-shell">
    @include('user.partials.account_nav', ['user' => $user])

    <section class="account-hero">
        <div>
            <p class="eyebrow">Employee Portal</p>
            <h1 class="account-title">Welcome back, {{ $user?->name ?? 'User' }}</h1>
            <p class="account-sub">Access your cloud phone, extension directory, and account security.</p>
            <div class="hero-actions" style="margin-top: 18px;">
                <button type="button" class="btn btn-primary" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Open Softphone</span>
                </button>
                <a class="btn btn-secondary" href="{{ route('user.pbx.phone') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Extension Directory</span>
                </a>
            </div>
        </div>

        <!-- Extension Status Highlight Card -->
        <div class="hero-card highlight-card">
            <div class="card-meta">Assigned Extension</div>
            @if($ext)
                <div class="card-value">{{ $ext->extension }}</div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="preview-dot" style="background-color: var(--site-success); width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                    <strong style="color: var(--site-success); font-size: 13px;">Online & Registered</strong>
                    <span class="text-muted" style="font-size: 12px;">({{ $ext->name }})</span>
                </div>
                <p class="card-sub">Ready to make and receive crystal-clear WebRTC audio calls.</p>
            @else
                <div class="card-value text-muted" style="font-size: 24px;">No Ext Assigned</div>
                <p class="card-sub text-warning" style="margin-top: 6px;">Contact your PBX administrator to assign an extension to your account.</p>
            @endif
        </div>
    </section>

    <!-- 4 Functional Cards with Clean SVG Icons -->
    <div class="dashboard-grid">
        <!-- 1. Web Dialer -->
        <div class="account-card">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
            </div>
            <h3 class="card-title">PBX Softphone</h3>
            <p class="card-sub">In-browser dialer with DTMF, hold, mute, and blind transfer.</p>
            <button type="button" class="btn btn-secondary btn-sm" style="margin-top: 14px;" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();">
                Launch Phone
            </button>
        </div>

        <!-- 2. Extension Directory -->
        <div class="account-card">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3 class="card-title">Colleague Directory</h3>
            <p class="card-sub">Browse internal employee extensions and initiate direct calls.</p>
            <a class="btn btn-secondary btn-sm" style="margin-top: 14px;" href="{{ route('user.pbx.phone') }}">
                View Colleagues
            </a>
        </div>

        <!-- 3. Profile Details -->
        <div class="account-card">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <h3 class="card-title">My Profile</h3>
            <p class="card-sub">Update your contact details, display name, and preferences.</p>
            <a class="btn btn-secondary btn-sm" style="margin-top: 14px;" href="{{ route('user.profile') }}">
                Edit Profile
            </a>
        </div>

        <!-- 4. Security -->
        <div class="account-card">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <h3 class="card-title">Account Security</h3>
            <p class="card-sub">Manage your password, session security, and authentication.</p>
            <a class="btn btn-secondary btn-sm" style="margin-top: 14px;" href="{{ route('user.security') }}">
                Security Settings
            </a>
        </div>
    </div>

    <!-- Account Telemetry & Extension Status -->
    <div class="account-card" style="padding: 24px;">
        <h3 class="card-title mb-3">{{ __('Telecom Network Insights') }}</h3>
        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label">{{ __('Signaling Gateway') }}</span>
                <span class="stat-value font-monospace" style="font-size: 13px; font-weight: 700; word-break: break-all;">pbx.103.107.160.37.sslip.io</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">{{ __('Signaling Protocol') }}</span>
                <span class="stat-value" style="font-size: 13px; font-weight: 700;">WSS (TLS Encrypted)</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">{{ __('Audio Protocol') }}</span>
                <span class="stat-value" style="font-size: 13px; font-weight: 700;">WebRTC / SRTP</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">{{ __('Parallel Ringing') }}</span>
                <span class="stat-value" style="font-size: 13px; font-weight: 700; color: var(--site-success);">Enabled (Multi-Tab)</span>
            </div>
        </div>
    </div>
</div>
@endsection
