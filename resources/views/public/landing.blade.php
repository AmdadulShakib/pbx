@php
    $appName = 'VoxPulse PBX';
    $adminLoggedIn = auth('admin')->check();
    $userLoggedIn = auth('web')->check();
@endphp

@extends('layouts.site')

@section('title', 'VoxPulse PBX - Enterprise Cloud Telecom & WebRTC Softphone')
@section('body-class', 'site-body')

@section('content')
<div class="site-shell">
    <!-- Top Navigation -->
    <header class="site-nav">
        <a class="brand" href="{{ url('/') }}">
            <img src="{{ asset('branding/logo.svg') }}" alt="VoxPulse PBX" height="38">
        </a>
        <nav class="nav-links">
            <a class="nav-link" href="#features">Features</a>
            <a class="nav-link" href="#architecture">Architecture</a>
            <a class="nav-link" href="#status">Live System</a>
            @if ($userLoggedIn)
                <a class="btn btn-secondary" href="{{ route('user.dashboard') }}">Employee Dashboard</a>
            @else
                <a class="btn btn-secondary" href="{{ route('login') }}">Employee Login</a>
            @endif
            @if ($adminLoggedIn)
                <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">Admin Portal</a>
            @else
                <a class="btn btn-primary" href="{{ route('admin.login') }}">Admin Login</a>
            @endif
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                <span>Enterprise Cloud Telecom & WebRTC</span>
            </div>
            <h1 class="hero-title">Crystal-clear enterprise calling, directly in your browser.</h1>
            <p class="hero-sub">
                VoxPulse PBX delivers carrier-grade SIP routing, zero-hardware web softphones,
                and multi-device parallel ringing. Connect your entire distributed team with sub-100ms connection times.
            </p>
            <div class="hero-actions">
                @if ($adminLoggedIn)
                    <a class="btn btn-primary btn-lg" href="{{ route('admin.dashboard') }}">
                        <span>Launch Admin Console</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                @else
                    <a class="btn btn-primary btn-lg" href="{{ route('admin.login') }}">
                        <span>Sign In as Administrator</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                @endif
                @if ($userLoggedIn)
                    <a class="btn btn-secondary btn-lg" href="{{ route('user.pbx.phone') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>Open Web Softphone</span>
                    </a>
                @else
                    <a class="btn btn-secondary btn-lg" href="{{ route('login') }}">
                        <span>Employee Portal</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Hero Telecom Softphone Mockup -->
        <div class="hero-preview-card">
            <div class="preview-header">
                <div class="preview-brand">
                    <span class="preview-dot online"></span>
                    <span class="preview-title">VoxPulse Softphone Node</span>
                </div>
                <span class="preview-badge">WSS Encrypted</span>
            </div>
            <div class="preview-body">
                <div class="preview-caller">
                    <div class="preview-avatar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="preview-meta">
                        <div class="preview-name">Admin Executive Desk</div>
                        <div class="preview-ext-row">
                            <span class="preview-ext">Ext 101</span>
                            <span class="preview-status">Active • Connected</span>
                        </div>
                    </div>
                </div>

                <div class="preview-stats-grid">
                    <div class="preview-stat">
                        <div class="stat-num">&lt; 100ms</div>
                        <div class="stat-desc">ICE Gathering</div>
                    </div>
                    <div class="preview-stat">
                        <div class="stat-num">Opus HD</div>
                        <div class="stat-desc">Audio Codec</div>
                    </div>
                    <div class="preview-stat">
                        <div class="stat-num">Multi-Tab</div>
                        <div class="stat-desc">Parallel Ringing</div>
                    </div>
                </div>

                <div class="preview-action-row">
                    <button type="button" class="btn btn-softphone-call" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>Launch Integrated Dialer</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Telecom Features Grid -->
    <section id="features" class="section">
        <div class="section-header">
            <span class="section-tag">Enterprise Telecom Capabilities</span>
            <h2 class="section-title">Built for modern communication standards.</h2>
            <p class="section-lead">
                Everything you need to manage internal company calling, extension provisioning, and team collaboration with zero physical PBX hardware.
            </p>
        </div>

        <div class="feature-grid">
            <!-- Feature 1 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <h3 class="feature-title">Native WebRTC Softphone</h3>
                <p class="feature-text">
                    Employees can place and receive internal calls directly through their web browser. No plugins, desktop downloads, or physical phones required.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <h3 class="feature-title">Simultaneous Multi-Device Ring</h3>
                <p class="feature-text">
                    When an extension is dialed, all open tabs and active employee devices ring simultaneously. When one answers, the others release automatically.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                </div>
                <h3 class="feature-title">Ultra-Fast ICE & Opus HD</h3>
                <p class="feature-text">
                    Direct media paths with sub-100ms ICE candidate negotiation and crystal-clear Opus audio codecs guarantee zero call lag and high fidelity.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3 class="feature-title">Colleague Directory & Transfer</h3>
                <p class="feature-text">
                    One-click colleague search, live presence visibility, and blind or attended call transfer capabilities right from the softphone drawer.
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h3 class="feature-title">Secure WSS & TLS Signaling</h3>
                <p class="feature-text">
                    All SIP signaling travels across TLS-encrypted WebSockets (`wss://`) with SRTP audio encryption for enterprise confidentiality.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <h3 class="feature-title">Live Extension Monitoring</h3>
                <p class="feature-text">
                    Real-time registration tracking, call state logs, and automated SIP endpoint management for administrators and telecom operators.
                </p>
            </div>
        </div>
    </section>

    <!-- Architecture & Telecom Stack -->
    <section id="architecture" class="section architecture-section">
        <div class="architecture-card">
            <div class="architecture-left">
                <span class="section-tag-light">Telecom Architecture</span>
                <h2 class="architecture-title">Engineered with Asterisk 20 & PJSIP WebRTC stack.</h2>
                <p class="architecture-text">
                    VoxPulse PBX leverages the industry-standard PJSIP SIP stack combined with modern WebRTC media handling.
                    No bulky proprietary desktop binaries—just pure standards-compliant telecom.
                </p>
                <div class="architecture-pills">
                    <span class="tech-pill">Asterisk 20.6 LTS</span>
                    <span class="tech-pill">PJSIP WebSockets</span>
                    <span class="tech-pill">RFC 5245 ICE</span>
                    <span class="tech-pill">RFC 6716 Opus Audio</span>
                    <span class="tech-pill">JsSIP 3.10 Engine</span>
                </div>
            </div>
            <div class="architecture-right">
                <div class="spec-row">
                    <span class="spec-label">Signaling Channel</span>
                    <span class="spec-value">Secure WebSocket (WSS)</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Audio Codecs</span>
                    <span class="spec-value">Opus, G.711u, G.711a</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">NAT Traversal</span>
                    <span class="spec-value">STUN (RFC 5389)</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Parallel Contacts</span>
                    <span class="spec-value">Up to 10 endpoints / ext</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Echo Cancellation</span>
                    <span class="spec-value">Hardware & WebRTC AEC</span>
                </div>
            </div>
        </div>
    </section>

    <!-- System Status Callout -->
    <section id="status" class="section">
        <div class="cta-box">
            <div class="cta-content">
                <div class="cta-status-badge">
                    <span class="cta-dot"></span>
                    <span>All Telecom Services Operational</span>
                </div>
                <h3 class="cta-title">Ready to empower your team with cloud telephony?</h3>
                <p class="cta-sub">
                    Sign in to your account or contact your PBX administrator to assign your extension credentials.
                </p>
            </div>
            <div class="cta-buttons">
                @if ($adminLoggedIn)
                    <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">Open Admin Console</a>
                @else
                    <a class="btn btn-primary" href="{{ route('admin.login') }}">Admin Login</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('login') }}">Employee Login</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="footer-brand">
                <img src="{{ asset('branding/logo.svg') }}" alt="VoxPulse PBX" height="32">
                <p class="footer-desc">
                    Enterprise Cloud PBX & WebRTC softphone platform for distributed teams.
                </p>
            </div>
            <div class="footer-links-group">
                <div class="footer-col">
                    <h5 class="footer-heading">Platform</h5>
                    <a href="#features">Features</a>
                    <a href="#architecture">Architecture</a>
                    <a href="{{ route('admin.login') }}">Admin Console</a>
                </div>
                <div class="footer-col">
                    <h5 class="footer-heading">Employee</h5>
                    <a href="{{ route('login') }}">Web Dialer Login</a>
                    <a href="{{ route('register') }}">Register Account</a>
                    <a href="{{ route('user.pbx.phone') }}">My Extension</a>
                </div>
                <div class="footer-col">
                    <h5 class="footer-heading">PBX Engine</h5>
                    <span class="footer-static">Asterisk 20 PJSIP</span>
                    <span class="footer-static">WebSockets WSS</span>
                    <span class="footer-static">Opus HD Voice</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-copy">&copy; {{ date('Y') }} VoxPulse PBX Telecom. All rights reserved.</div>
            <div class="footer-pill">
                <span class="footer-status-dot"></span>
                <span>System Online • 103.107.160.37</span>
            </div>
        </div>
    </footer>
</div>
@endsection
