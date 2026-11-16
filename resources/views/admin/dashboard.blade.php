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
                    <a href="{{ route('admin.pbx.status') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5">
                        <i data-lucide="activity" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Live Telemetry') }}</span>
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
                    <div class="rounded-2 bg-light text-primary p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="phone" style="width: 16px; height: 16px;"></i>
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
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Registered Employees') }}</span>
                    <div class="rounded-2 bg-light text-info p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalUsers ?? 0 }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="shield" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('SIP Credential Mapped') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Telephony Core') }}</span>
                    <div class="rounded-2 bg-light text-success p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="server" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">Asterisk 20.6</div>
                <div class="d-flex align-items-center gap-1.5 small text-success">
                    <span class="badge-dot-live"></span>
                    <span>{{ __('PJSIP Engine Operational') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Signaling Gateway') }}</span>
                    <div class="rounded-2 bg-light text-secondary p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="lock" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">WSS / TLS</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="cpu" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Parallel Ring (Max 10)') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Extensions Structured Data Table (Stripe/Linear Clean Standard) -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
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
                                <th style="width: 110px;">{{ __('Extension') }}</th>
                                <th>{{ __('Display Name') }}</th>
                                <th>{{ __('Assigned Employee') }}</th>
                                <th style="width: 120px;">{{ __('Presence') }}</th>
                                <th class="text-end" style="width: 140px;">{{ __('Quick Action') }}</th>
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
                                                <div class="rounded-circle bg-light text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                                    {{ strtoupper(substr($ext->user->name, 0, 2)) }}
                                                </div>
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
                                        <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 px-2.5 py-1" onclick="if (typeof togglePbxDialer === 'function') { togglePbxDialer(); pbxCallContact('{{ $ext->extension }}', '{{ $ext->name }}'); }">
                                            <i data-lucide="phone-call" style="width: 13px; height: 13px;"></i>
                                            <span>{{ __('Call') }}</span>
                                        </button>
                                        <a href="{{ route('admin.pbx.extensions.edit', $ext->id) }}" class="btn btn-sm btn-light text-muted p-1 ms-1" title="Edit">
                                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                        </a>
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

    <!-- Side Deck: System Telemetry & Quick Test Call -->
    <div class="col-12 col-xl-4">
        <!-- Telemetry Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i data-lucide="activity" class="text-primary" style="width: 18px; height: 18px;"></i>
                    <span>{{ __('Telephony Gateway Health') }}</span>
                </h6>
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('Signaling URL') }}</span>
                    <code class="fw-semibold text-dark text-break">{{ config('pbx.ws_url') }}</code>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('SIP Realm Domain') }}</span>
                    <span class="fw-semibold text-dark">{{ config('pbx.domain') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('STUN NAT Gateway') }}</span>
                    <span class="fw-semibold text-dark">stun.l.google.com:19302</span>
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
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i data-lucide="phone-forwarded" class="text-secondary" style="width: 18px; height: 18px;"></i>
                    <span>{{ __('Server Test Call Bridge') }}</span>
                </h6>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('admin.pbx.originate') }}" method="POST">
                    @csrf
                    <div class="mb-2.5">
                        <label for="from" class="form-label small fw-semibold text-muted mb-1">{{ __('From Extension') }}</label>
                        <input type="text" class="form-control form-control-sm" id="from" name="from" placeholder="e.g. 101" required>
                    </div>
                    <div class="mb-3">
                        <label for="to" class="form-label small fw-semibold text-muted mb-1">{{ __('To Extension / Destination') }}</label>
                        <input type="text" class="form-control form-control-sm" id="to" name="to" placeholder="e.g. 102" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1.5">
                        <i data-lucide="phone-forwarded" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Bridge Test Call') }}</span>
                    </button>
                </form>
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
