@php
    $appName = config('app.name', 'Dynamic Panel');
    $adminLoggedIn = auth('admin')->check();
    $userLoggedIn = auth()->check();
@endphp

@extends('layouts.site')

@section('title', $appName . ' - Home')
@section('body-class', 'site-body')

@section('content')
<div class="site-shell">
    <header class="site-nav">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-mark">{{ strtoupper(substr($appName, 0, 1)) }}</span>
            <span>{{ $appName }}</span>
        </a>
        <nav class="nav-links">
            <a class="nav-link" href="#features">Features</a>
            <a class="nav-link" href="#workflow">Workflow</a>
            @if ($userLoggedIn)
                <a class="btn btn-ghost" href="{{ route('user.dashboard') }}">User Dashboard</a>
            @else
                <a class="btn btn-ghost" href="{{ route('login') }}">User Login</a>
            @endif
            @if ($adminLoggedIn)
                <a class="btn btn-accent" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
            @else
                <a class="btn btn-accent" href="{{ route('admin.login') }}">Admin Login</a>
            @endif
        </nav>
    </header>

    <section class="hero">
        <div>
            <h1 class="hero-title">Launch data-driven panels faster with clean defaults.</h1>
            <p class="hero-sub">
                Generate admin, user, and public experiences from a single configuration. Create
                modules, APIs, and routes with consistent ownership rules and polished UI out of the box.
            </p>
            <div class="hero-actions">
                @if ($adminLoggedIn)
                    <a class="btn btn-warm" href="{{ route('admin.dashboard') }}">Open Admin</a>
                @else
                    <a class="btn btn-warm" href="{{ route('admin.login') }}">Open Admin</a>
                @endif
                @if ($userLoggedIn)
                    <a class="btn btn-ghost" href="{{ route('user.dashboard') }}">User Dashboard</a>
                @else
                    <a class="btn btn-ghost" href="{{ route('register') }}">Create User Account</a>
                @endif
            </div>
        </div>
        <div class="hero-card">
            <div class="feature-tag">Ready to build</div>
            <h3 class="feature-title">Everything wired for you.</h3>
            <p class="feature-text">
                Generate pages, controllers, and APIs with sensible defaults, secure scopes,
                and a clean structure that scales with your modules.
            </p>
            <div class="stat-grid">
                <div class="stat">
                    <div class="stat-label">Scopes supported</div>
                    <div class="stat-value">Admin, User, Public</div>
                </div>
                <div class="stat">
                    <div class="stat-label">API readiness</div>
                    <div class="stat-value">Postman + Docs</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Ownership rules</div>
                    <div class="stat-value">Secure by default</div>
                </div>
                <div class="stat">
                    <div class="stat-label">UI kit</div>
                    <div class="stat-value">Clean + modern</div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="section">
        <h2 class="section-title">Built for modular products</h2>
        <p class="section-lead">
            Define modules once and get routes, controllers, views, and API collections generated
            automatically with consistent naming and guard rules.
        </p>
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-tag">Route-ready</span>
                <h3 class="feature-title">Scoped routing</h3>
                <p class="feature-text">Admin, user, and public paths stay clean and predictable across web and API.</p>
            </div>
            <div class="feature-card">
                <span class="feature-tag">Ownership</span>
                <h3 class="feature-title">Secure data access</h3>
                <p class="feature-text">User and guest access stays locked with ownership rules baked into controllers.</p>
            </div>
            <div class="feature-card">
                <span class="feature-tag">UI defaults</span>
                <h3 class="feature-title">Polished screens</h3>
                <p class="feature-text">A tasteful landing, auth, and profile setup keeps projects launch-ready.</p>
            </div>
        </div>
    </section>

    <section id="workflow" class="section">
        <h2 class="section-title">Simple workflow, strong output</h2>
        <p class="section-lead">
            Configure your modules, generate, and ship. The structure stays consistent and
            easy to extend without reworking core files.
        </p>
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-tag">1</span>
                <h3 class="feature-title">Define modules</h3>
                <p class="feature-text">Describe fields, endpoints, and scopes in a single JSON config.</p>
            </div>
            <div class="feature-card">
                <span class="feature-tag">2</span>
                <h3 class="feature-title">Generate output</h3>
                <p class="feature-text">Controllers, routes, views, and assets are built with one action.</p>
            </div>
            <div class="feature-card">
                <span class="feature-tag">3</span>
                <h3 class="feature-title">Deliver fast</h3>
                <p class="feature-text">Use the default UI or expand with your own brand in minutes.</p>
            </div>
        </div>
    </section>

    <section class="cta">
        <div>
            <h3 class="cta-title">Ready to explore?</h3>
            <p class="cta-text">Jump into the admin panel or create a user account to see the flow.</p>
        </div>
        <div class="hero-actions">
            @if ($userLoggedIn)
                <a class="btn btn-ghost" href="{{ route('user.dashboard') }}">User Dashboard</a>
            @else
                <a class="btn btn-ghost" href="{{ route('login') }}">User Login</a>
            @endif
            @if ($adminLoggedIn)
                <a class="btn btn-warm" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
            @else
                <a class="btn btn-warm" href="{{ route('admin.login') }}">Admin Login</a>
            @endif
        </div>
    </section>

    <footer class="footer">
        <span>{{ $appName }} by <a href="https://adminpanelbuilder.com" target="_blank" rel="noopener">AdminPanelBuilder</a></span>
    </footer>
</div>
@endsection
