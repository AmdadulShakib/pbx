@extends('admin.layouts.app')
@section('title', __('PBX Operations Dashboard'))
@section('page-title', __('PBX Operations'))

@section('content')
<div class="row g-3">
    <!-- Top Action Deck -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="radio" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('VoxPulse Telecom Engine') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Carrier-grade PJSIP WebRTC signaling with multi-device parallel ring.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();">
                        <i data-lucide="phone-call" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Launch Softphone') }}</span>
                    </button>
                    <a href="{{ route('admin.pbx.live-calls') }}" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5">
                        <i data-lucide="radio" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Live Calls') }}</span>
                    </a>
                    <a href="{{ route('admin.pbx.calls.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5">
                        <i data-lucide="history" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Call History') }}</span>
                    </a>
                    <a href="{{ route('admin.pbx.extensions.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5">
                        <i data-lucide="plus" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Add Extension') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Headline KPI Cards (F-Pattern Architecture) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Total Extensions') }}</span>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="phone" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalExtensions ?? 0 }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-success">
                    <i data-lucide="check-circle" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('100% Provisioned & Active') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Carrier SIP Trunks') }}</span>
                    <div class="rounded-3 bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="network" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalTrunks ?? 0 }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="shield-check" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Twilio • Telnyx • BTCL') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Routing Groups & IVR') }}</span>
                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="layers" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalRingGroups ?? 0 }} <span class="fs-6 text-muted fw-normal">/ {{ $totalIvrs ?? 0 }} IVRs</span></div>
                <div class="d-flex align-items-center gap-1.5 small text-success">
                    <span class="badge-dot-live"></span>
                    <span>{{ __('Multi-Tier Call Queues') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Total CDR Call Logs') }}</span>
                    <div class="rounded-3 bg-secondary-subtle text-secondary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="history" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalCalls ?? 0 }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="activity" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Inbound & Outbound Traffic') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Company Extensions (7 cols) + System Telemetry & Test Bridge (5 cols) -->
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="phone" class="text-primary" style="width: 18px; height: 18px;"></i>
                        <span>{{ __('Company Extensions & Presence') }}</span>
                    </h6>
                    <small class="text-muted">{{ __('Direct extension dialing and internal SIP assignments') }}</small>
                </div>
                <a href="{{ route('admin.pbx.extensions.index') }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('Manage All') }}
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">{{ __('Extension') }}</th>
                                <th>{{ __('Display Name') }}</th>
                                <th>{{ __('Assigned User') }}</th>
                                <th style="width: 110px;">{{ __('Presence') }}</th>
                                <th class="text-end" style="width: 120px;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($extensions as $ext)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold fs-6 px-2.5 py-1">
                                            Ext {{ $ext->extension }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $ext->name }}</div>
                                        <small class="text-muted">WebRTC • Opus HD</small>
                                    </td>
                                    <td>
                                        @if($ext->user)
                                            <div class="d-flex align-items-center gap-2">
                                                @if($ext->user->avatar_url)
                                                    <img src="{{ $ext->user->avatar_url }}" alt="{{ $ext->user->name }}" class="rounded-circle border" style="width: 30px; height: 30px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                                    <div class="rounded-circle bg-primary-subtle text-primary border border-primary-subtle d-none d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 30px; height: 30px; font-size: 11px;">
                                                        {{ strtoupper(substr($ext->user->name, 0, 2)) }}
                                                    </div>
                                                @else
                                                    <div class="rounded-circle bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 30px; height: 30px; font-size: 11px;">
                                                        {{ strtoupper(substr($ext->user->name, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="small fw-semibold text-dark">{{ $ext->user->name }}</div>
                                                    <div class="text-muted" style="font-size: 11px;">{{ $ext->user->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary small">{{ __('Unassigned') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="d-inline-flex align-items-center gap-1.5 text-success small fw-semibold">
                                            <span class="badge-dot-live"></span>
                                            <span>{{ __('Online') }}</span>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success btn-action-icon" 
                                                    onclick="if (typeof togglePbxDialer === 'function') { togglePbxDialer(); pbxCallContact('{{ $ext->extension }}', '{{ $ext->name }}'); }" 
                                                    title="{{ __('Call Ext') }} {{ $ext->extension }}" 
                                                    data-bs-toggle="tooltip">
                                                <i data-lucide="phone-call"></i>
                                            </button>
                                            <a href="{{ route('admin.pbx.extensions.edit', $ext->id) }}" 
                                               class="btn btn-sm btn-outline-secondary btn-action-icon" 
                                               title="{{ __('Edit Extension') }}" 
                                               data-bs-toggle="tooltip">
                                                <i data-lucide="pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i data-lucide="phone-off" class="mb-2 text-secondary" style="width: 32px; height: 32px;"></i>
                                        <p class="mb-1">{{ __('No extensions provisioned yet.') }}</p>
                                        <a href="{{ route('admin.pbx.extensions.create') }}" class="btn btn-primary btn-sm mt-1">
                                            <i data-lucide="plus" class="me-1" style="width: 13px; height: 13px;"></i>
                                            {{ __('Create Extension') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Deck: System Telemetry & Quick Test Call (5 cols) -->
    <div class="col-12 col-xl-5">
        <!-- Telemetry Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i data-lucide="activity" class="text-primary" style="width: 18px; height: 18px;"></i>
                    <span>{{ __('Telephony Gateway Health') }}</span>
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-2 font-monospace">{{ __('Asterisk PJSIP') }}</span>
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('Signaling URL') }}</span>
                    <code class="fw-semibold text-break">{{ config('pbx.ws_url') }}</code>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('SIP Realm Domain') }}</span>
                    <span class="fw-semibold font-monospace">{{ config('pbx.domain') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('STUN NAT Gateway') }}</span>
                    <span class="fw-semibold">stun.l.google.com:19302</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('Parallel Contacts') }}</span>
                    <span class="fw-semibold text-success">{{ __('Enabled (Max 10 Contacts)') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">{{ __('Media Encryption') }}</span>
                    <span class="fw-semibold text-dark">SRTP / DTLS 1.2</span>
                </div>
            </div>
        </div>

        <!-- Quick Call Originate Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i data-lucide="phone-forwarded" class="text-primary" style="width: 18px; height: 18px;"></i>
                    <span>{{ __('Server Test Call Bridge') }}</span>
                </h6>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('admin.pbx.originate') }}" method="POST">
                    @csrf
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="from" class="form-label small fw-semibold text-muted mb-1">{{ __('From Extension') }}</label>
                            <input type="text" class="form-control form-control-sm font-monospace" id="from" name="from" placeholder="e.g. 101" required>
                        </div>
                        <div class="col-6">
                            <label for="to" class="form-label small fw-semibold text-muted mb-1">{{ __('To Extension / Target') }}</label>
                            <input type="text" class="form-control form-control-sm font-monospace" id="to" name="to" placeholder="e.g. 102" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2">
                        <i data-lucide="phone-forwarded" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Bridge Test Call') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Full-Width: Recent Call Activity Card (CDR Real-Time Snapshot) -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="history" class="text-primary" style="width: 18px; height: 18px;"></i>
                        <span>{{ __('Recent Call Activity (CDR)') }}</span>
                    </h6>
                    <small class="text-muted">{{ __('Latest incoming, outgoing, and internal call records') }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border d-none d-sm-inline-flex align-items-center gap-1 font-monospace">
                        <i data-lucide="activity" style="width: 12px; height: 12px;"></i>
                        Live CDR
                    </span>
                    <a href="{{ route('admin.pbx.calls.index') }}" class="btn btn-outline-secondary btn-sm">
                        {{ __('View All Calls') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Caller') }}</th>
                                <th>{{ __('Destination') }}</th>
                                <th>{{ __('Direction') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Timestamp') }}</th>
                                <th class="text-end pe-3 pe-md-4" style="width: 80px;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCalls as $call)
                                @php
                                    $callerUser = $call->callerExtension?->user ?? $call->user;
                                    $callerAvatar = $callerUser?->avatar_url;
                                    $callerInitial = strtoupper(substr($call->caller_name ?: ($callerUser?->name ?: $call->caller), 0, 1));
                                    $callerDisplayName = $call->caller_name ?: ($callerUser?->name ?: ('Ext ' . $call->caller));

                                    $calleeUser = $call->calleeExtension?->user;
                                    $calleeAvatar = $calleeUser?->avatar_url;
                                    $calleeInitial = strtoupper(substr($call->callee_name ?: ($calleeUser?->name ?: $call->callee), 0, 1));
                                    $calleeDisplayName = $call->callee_name ?: ($calleeUser?->name ?: ('Ext ' . $call->callee));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            @if($callerAvatar)
                                                <img src="{{ $callerAvatar }}" alt="{{ $callerDisplayName }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                                <div class="rounded-circle bg-primary-subtle text-primary border border-primary-subtle d-none d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px;">
                                                    {{ $callerInitial }}
                                                </div>
                                            @else
                                                <div class="rounded-circle bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px;">
                                                    {{ $callerInitial }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark small" style="line-height: 1.2;">{{ $callerDisplayName }}</div>
                                                <span class="badge bg-secondary-subtle text-secondary py-0 px-1.5 font-monospace" style="font-size: 9.5px;">Ext {{ $call->caller }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            @if($calleeAvatar)
                                                <img src="{{ $calleeAvatar }}" alt="{{ $calleeDisplayName }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                                <div class="rounded-circle bg-info-subtle text-info border border-info-subtle d-none d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px;">
                                                    {{ $calleeInitial }}
                                                </div>
                                            @else
                                                <div class="rounded-circle bg-info-subtle text-info border border-info-subtle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px;">
                                                    {{ $calleeInitial }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark small" style="line-height: 1.2;">{{ $calleeDisplayName }}</div>
                                                <span class="badge bg-secondary-subtle text-secondary py-0 px-1.5 font-monospace" style="font-size: 9.5px;">Ext {{ $call->callee }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border text-uppercase font-monospace" style="font-size: 10px;">
                                            {{ $call->direction }}
                                        </span>
                                    </td>
                                    <td class="font-monospace small">
                                        {{ gmdate('i:s', $call->duration) }}
                                    </td>
                                    <td>
                                        @if($call->status === 'answered')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 11px;">
                                                <i data-lucide="check" class="me-1 d-none d-sm-inline" style="width: 12px; height: 12px;"></i>
                                                {{ __('Answered') }}
                                            </span>
                                        @elseif($call->status === 'busy')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1" style="font-size: 11px;">
                                                <i data-lucide="clock" class="me-1 d-none d-sm-inline" style="width: 12px; height: 12px;"></i>
                                                {{ __('Busy') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1" style="font-size: 11px;">
                                                <i data-lucide="x" class="me-1 d-none d-sm-inline" style="width: 12px; height: 12px;"></i>
                                                {{ __('No Answer') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            {{ \Carbon\Carbon::parse($call->created_at)->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 pe-md-4">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success btn-action-icon" 
                                                onclick="if (typeof triggerPbxCall === 'function') { triggerPbxCall('{{ $call->callee }}'); } else if (typeof togglePbxDialer === 'function') { togglePbxDialer(); pbxCallContact('{{ $call->callee }}', '{{ $calleeDisplayName }}'); }" 
                                                title="{{ __('Call') }} {{ $call->callee }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="phone-call"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i data-lucide="inbox" class="mb-2 text-secondary" style="width: 32px; height: 32px;"></i>
                                        <p class="mb-0">{{ __('No call activity logged yet.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.letter-spacing-1 {
    letter-spacing: 0.5px;
}
.badge-dot-live {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: var(--bs-success, #16a34a);
    display: inline-block;
}
</style>
@endsection
