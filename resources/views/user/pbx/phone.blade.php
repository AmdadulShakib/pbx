@extends('layouts.site')

@section('title', __('Company Extension Directory & Web Phone'))
@section('body-class', 'account-body')

@section('content')
<div class="account-shell">
    @if(auth('web')->check())
        @include('user.partials.account_nav', ['user' => auth('web')->user()])
    @endif

    <section class="account-hero">
        <div>
            <p class="eyebrow">{{ __('Internal Communications') }}</p>
            <h1 class="account-title">{{ __('PBX Web Phone & Directory') }}</h1>
            <p class="account-sub">{{ __('High-definition browser audio calling across internal extensions and departments.') }}</p>
        </div>
        <div class="hero-card highlight-card">
            <div class="card-meta">{{ __('My Assigned Extension') }}</div>
            <div class="card-value font-monospace">{{ $ext ? 'Ext ' . $ext->extension : __('None') }}</div>
            <p class="card-sub">
                @if($ext)
                    <span class="text-success fw-bold d-inline-flex align-items-center gap-1">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
                        {{ __('Online & Registered') }}
                    </span> &bull; {{ $ext->name }}
                @else
                    <span class="text-warning fw-bold d-inline-flex align-items-center gap-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ __('No extension assigned') }}
                    </span>
                @endif
            </p>
        </div>
    </section>

    <!-- Top Quick Action Cards -->
    <div class="dashboard-grid mb-4">
        <div class="account-card" style="--delay: 1;">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
            </div>
            <h3 class="card-title">{{ __('Web Dialer') }}</h3>
            <p class="card-sub">{{ __('Make internal calls, transfer, hold, or send DTMF keys using the built-in softphone.') }}</p>
            <button class="btn btn-accent btn-sm" type="button" onclick="togglePbxDialer()">
                {{ __('Open Softphone') }}
            </button>
        </div>

        <div class="account-card" style="--delay: 2;">
            <div class="card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3 class="card-title">{{ __('Colleague Directory') }}</h3>
            <p class="card-sub">{{ __(':count active employee extensions registered in your PBX network.', ['count' => count($contacts)]) }}</p>
            <button class="btn btn-ghost btn-sm" type="button" onclick="document.getElementById('directory-search-input').focus()">
                {{ __('Search Colleagues') }}
            </button>
        </div>
    </div>

    <!-- Full-Width Corporate Extension Directory -->
    <div class="account-card" style="--delay: 3; padding: 24px;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
            <div>
                <h3 class="card-title mb-1">{{ __('Corporate Extension Directory') }}</h3>
                <p class="text-muted small mb-0">{{ __('Click any colleague to initiate an immediate internal audio call.') }}</p>
            </div>
            <div style="min-width: 260px;">
                <input type="text" id="directory-search-input" class="form-control form-control-sm" placeholder="{{ __('Search by name or extension...') }}" oninput="filterDirectory(this.value)">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="directory-table">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="ps-2 py-2.5">{{ __('Employee') }}</th>
                        <th class="py-2.5">{{ __('Extension') }}</th>
                        <th class="py-2.5">{{ __('Email Address') }}</th>
                        <th class="py-2.5">{{ __('Status') }}</th>
                        <th class="text-end pe-2 py-2.5">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $c)
                        <tr class="directory-row" data-name="{{ strtolower($c->name . ' ' . ($c->user?->name ?? '')) }}" data-ext="{{ $c->extension }}">
                            <td class="ps-2 py-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 13px;">
                                        {{ strtoupper(substr($c->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $c->name }}</div>
                                        @if($c->user)
                                            <small class="text-muted d-block" style="font-size: 12px;">{{ $c->user->name }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1" style="font-size: 13px;">
                                    Ext {{ $c->extension }}
                                </span>
                            </td>
                            <td class="py-3 text-muted small">
                                {{ $c->user?->email ?? __('Internal Desk') }}
                            </td>
                            <td class="py-3">
                                <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2 py-0.5">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: var(--site-success, #16a34a);"></span>
                                    <span>{{ __('Available') }}</span>
                                </span>
                            </td>
                            <td class="text-end pe-2 py-3">
                                <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1.5 px-3 py-1" onclick="togglePbxDialer(); pbxCallContact('{{ $c->extension }}', '{{ $c->name }}')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span>{{ __('Call') }}</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                {{ __('No colleague extensions registered yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterDirectory(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.directory-row');
        rows.forEach(r => {
            const name = r.getAttribute('data-name') || '';
            const ext = r.getAttribute('data-ext') || '';
            if (!q || name.includes(q) || ext.includes(q)) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
    }
</script>
@endsection
