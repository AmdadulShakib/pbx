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
                    <span class="text-success fw-bold">● {{ __('Active & Assigned') }}</span> ({{ $ext->name }})
                @else
                    <span class="text-warning fw-bold">⚠ {{ __('No extension assigned yet') }}</span>
                @endif
            </p>
        </div>
    </section>

    <div class="dashboard-grid">
        <div class="account-card" style="--delay: 1;">
            <div class="card-icon">📞</div>
            <h3 class="card-title">{{ __('Web Dialer') }}</h3>
            <p class="card-sub">{{ __('The floating dialer is located at the bottom-right corner of your screen.') }}</p>
            <button class="btn btn-accent" type="button" onclick="togglePbxDialer()">
                {{ __('Open Web Dialer') }}
            </button>
        </div>

        <div class="account-card" style="--delay: 2;">
            <div class="card-icon">👥</div>
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
                                    <button class="btn btn-sm btn-outline-success" onclick="togglePbxDialer(); pbxCallContact('{{ $c->extension }}', '{{ $c->name }}')">
                                        📞 {{ __('Call') }}
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
