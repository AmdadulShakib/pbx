@extends('layouts.site')

@section('title', __('My PBX Extension & Phone'))
@section('body-class', 'account-body')

@section('content')
<div class="account-shell">
    @if(auth('web')->check())
        @include('user.partials.account_nav', ['user' => auth('web')->user()])
    @endif

    <section class="account-hero">
        <div>
            <p class="eyebrow">{{ __('Internal Communications') }}</p>
            <h1 class="account-title">{{ __('PBX Web Phone & Extension') }}</h1>
            <p class="account-sub">{{ __('Make and receive internal HD audio calls directly from your browser.') }}</p>
        </div>
        <div class="hero-card highlight-card">
            <div class="card-meta">{{ __('My Extension') }}</div>
            <div class="card-value">{{ $ext ? $ext->extension : __('None') }}</div>
            <p class="card-sub">
                @if($ext)
                    <span class="text-success fw-bold d-inline-flex align-items-center gap-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
                        {{ __('Active & Assigned') }}
                    </span> ({{ $ext->name }})
                @else
                    <span class="text-warning fw-bold d-inline-flex align-items-center gap-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        {{ __('No extension assigned yet') }}
                    </span>
                @endif
            </p>
        </div>
    </section>

    <div class="dashboard-grid">
        <div class="account-card" style="--delay: 1;">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
            </div>
            <h3 class="card-title">{{ __('Web Dialer') }}</h3>
            <p class="card-sub">{{ __('The floating dialer is located at the bottom-right corner of your screen.') }}</p>
            <button class="btn btn-accent" type="button" onclick="togglePbxDialer()">
                {{ __('Open Web Dialer') }}
            </button>
        </div>

        <div class="account-card" style="--delay: 2;">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3 class="card-title">{{ __('Colleagues') }}</h3>
            <p class="card-sub">{{ __(':count active colleagues available to call.', ['count' => count($contacts)]) }}</p>
            <button class="btn btn-ghost" type="button" onclick="togglePbxDialer(); switchPbxTab('contacts');">
                {{ __('View Contacts') }}
            </button>
        </div>
    </div>

    <div class="account-grid">
        <section class="account-card wide" style="--delay: 3;">
            <h3 class="card-title">{{ __('Company Extension Directory') }}</h3>
            <div class="table-responsive mt-3">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Extension') }}</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $c)
                            <tr>
                                <td>
                                    <span class="badge bg-primary text-white fs-6 px-2 py-1">{{ $c->extension }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $c->name }}</div>
                                    @if($c->user)
                                        <small class="text-muted">{{ $c->user->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $c->user?->email ?? __('N/A') }}</small>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" onclick="togglePbxDialer(); pbxCallContact('{{ $c->extension }}', '{{ $c->name }}')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        <span>{{ __('Call') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    {{ __('No other colleagues registered yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="account-card soft-card" style="--delay: 4;">
            <h3 class="card-title">{{ __('Connection Guide') }}</h3>
            <p class="card-sub mb-3">{{ __('Important requirements for browser calling:') }}</p>
            <ul class="small ps-3 text-muted">
                <li class="mb-2"><strong>{{ __('Microphone Access') }}:</strong> {{ __('Allow browser microphone permission when prompted.') }}</li>
                <li class="mb-2"><strong>{{ __('Automatic Login') }}:</strong> {{ __('Your dialer connects automatically in the background using your extension.') }}</li>
                <li class="mb-2"><strong>{{ __('Floating Phone') }}:</strong> {{ __('The dialer stays in the bottom corner across all pages.') }}</li>
            </ul>
        </aside>
    </div>
</div>
@endsection
