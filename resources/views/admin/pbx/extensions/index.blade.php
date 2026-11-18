@extends('admin.layouts.app')
@section('title', __('PBX Extensions'))
@section('page-title', __('PBX Extensions'))

@section('content')
<div class="row g-3">
    <div class="col-12">
        @if(session('ok'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i data-lucide="check-circle" class="me-2"></i>{{ session('ok') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('err'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i data-lucide="alert-triangle" class="me-2"></i>{{ session('err') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold">
                        <i data-lucide="phone" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
                        {{ __('Extension List') }}
                    </h5>
                    <small class="text-muted">{{ __('Manage internal employee extensions and SIP credentials') }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.pbx.status') }}" class="btn btn-outline-info btn-sm">
                        <i data-lucide="activity" class="me-1" style="width: 16px; height: 16px;"></i>
                        {{ __('PBX Live Status') }}
                    </a>
                    <a href="{{ route('admin.pbx.extensions.create') }}" class="btn btn-primary btn-sm">
                        <i data-lucide="plus" class="me-1" style="width: 16px; height: 16px;"></i>
                        {{ __('Add Extension') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-custom-head text-muted small text-uppercase">
                            <tr>
                                <th style="width: 120px;">{{ __('Extension') }}</th>
                                <th>{{ __('Display Name') }}</th>
                                <th>{{ __('Assigned Employee') }}</th>
                                <th style="width: 120px;">{{ __('Status') }}</th>
                                <th style="width: 160px;">{{ __('Created At') }}</th>
                                <th style="width: 140px;" class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($extensions as $ext)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-1 fw-bold">
                                            {{ $ext->extension }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $ext->name }}</span>
                                    </td>
                                    <td>
                                        @if($ext->user)
                                            <div class="d-flex align-items-center gap-2">
                                                @if($ext->user->avatar_url)
                                                    <img src="{{ $ext->user->avatar_url }}" alt="{{ $ext->user->name }}" class="rounded-circle border" style="width: 34px; height: 34px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle fw-bold d-none d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 13px;">
                                                        {{ strtoupper(substr($ext->user->name, 0, 1)) }}
                                                    </div>
                                                @else
                                                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 13px;">
                                                        {{ strtoupper(substr($ext->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $ext->user->name }}</div>
                                                    <small class="text-muted">{{ $ext->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Unassigned') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ext->enabled)
                                            <span class="badge bg-success-subtle text-success">
                                                <i data-lucide="check" class="me-1" style="width: 12px; height: 12px;"></i>{{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i data-lucide="x" class="me-1" style="width: 12px; height: 12px;"></i>{{ __('Disabled') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $ext->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                            <!-- Device Setup Modal Button (Icon Only) -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center" 
                                                    style="width: 32px; height: 32px; border-radius: 8px;" 
                                                    onclick="openDeviceSetupModal('{{ $ext->extension }}', '{{ addslashes($ext->name) }}', '{{ addslashes($ext->password) }}', '{{ addslashes($ext->user?->name ?? '') }}')" 
                                                    title="{{ __('Telephone Device & IP Phone Setup') }}" 
                                                    data-bs-toggle="tooltip">
                                                <i data-lucide="smartphone" style="width: 15px; height: 15px;"></i>
                                            </button>

                                            <!-- WebRTC Softphone Call Button (Icon Only) -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success p-0 d-inline-flex align-items-center justify-content-center" 
                                                    style="width: 32px; height: 32px; border-radius: 8px;" 
                                                    onclick="if (typeof togglePbxDialer === 'function') { togglePbxDialer(); pbxCallContact('{{ $ext->extension }}', '{{ $ext->name }}'); }" 
                                                    title="{{ __('Call with Softphone') }}" 
                                                    data-bs-toggle="tooltip">
                                                <i data-lucide="phone-call" style="width: 15px; height: 15px;"></i>
                                            </button>

                                            <!-- Edit Extension Button (Icon Only) -->
                                            <a href="{{ route('admin.pbx.extensions.edit', $ext) }}" 
                                               class="btn btn-sm btn-outline-secondary p-0 d-inline-flex align-items-center justify-content-center" 
                                               style="width: 32px; height: 32px; border-radius: 8px;" 
                                               title="{{ __('Edit Extension') }}" 
                                               data-bs-toggle="tooltip">
                                                <i data-lucide="pencil" style="width: 15px; height: 15px;"></i>
                                            </a>

                                            <!-- Delete Extension Button (Icon Only) -->
                                            <form action="{{ route('admin.pbx.extensions.destroy', $ext) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('{{ __('Are you sure you want to delete this extension?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center" 
                                                        style="width: 32px; height: 32px; border-radius: 8px;" 
                                                        title="{{ __('Delete Extension') }}" 
                                                        data-bs-toggle="tooltip">
                                                    <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i data-lucide="phone-off" class="mb-2 text-secondary" style="width: 40px; height: 40px;"></i>
                                            <h6>{{ __('No Extensions Found') }}</h6>
                                            <p class="small mb-3">{{ __('Get started by creating your first employee extension.') }}</p>
                                            <a href="{{ route('admin.pbx.extensions.create') }}" class="btn btn-primary btn-sm">
                                                <i data-lucide="plus" class="me-1"></i>{{ __('Create Extension') }}
                                            </a>
                                        </div>
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

<!-- IP Phone & Telephone Device Configuration Modal -->
<div class="modal fade" id="deviceSetupModal" tabindex="-1" aria-labelledby="deviceSetupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="smartphone" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="deviceSetupModalLabel">
                            {{ __('IP Phone & Telephone Device Configuration') }}
                        </h5>
                        <small class="text-white-50">{{ __('Extension') }} <span id="mExtHeaderBadge" class="badge bg-light text-primary font-monospace fw-bold"></span></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Quick Notice -->
                <div class="alert alert-info d-flex align-items-start gap-2.5 p-3 mb-4 rounded-3 border-0 bg-info-subtle">
                    <i data-lucide="info" class="text-info mt-0.5 flex-shrink-0" style="width: 18px; height: 18px;"></i>
                    <div class="small text-info-emphasis">
                        {{ __('Use these credentials to register any hardware VoIP deskphone (Yealink, Grandstream, Fanvil, Cisco) or mobile/desktop softphone (Zoiper, MicroSIP, GS Wave, Linphone) to this extension.') }}
                    </div>
                </div>

                <!-- SIP Credentials Card -->
                <div class="card border mb-4 rounded-3">
                    <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <span class="fw-bold small text-uppercase text-secondary letter-spacing-1">
                            <i data-lucide="key" class="me-1 text-primary" style="width: 14px; height: 14px;"></i>{{ __('SIP Account Credentials') }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-2 font-monospace">{{ __('Status: Ready') }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- SIP Server / Domain -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('SIP Server / Host / Domain') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control font-monospace" id="mSipServer" value="{{ config('pbx.domain', request()->getHost()) }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyInputVal('mSipServer')">
                                        <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Extension / Username -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('SIP Extension / Username / User ID') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control font-monospace fw-bold text-primary" id="mExtension" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyInputVal('mExtension')">
                                        <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- SIP Password -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('SIP Secret / Password') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" class="form-control font-monospace" id="mPassword" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleModalPass()">
                                        <i data-lucide="eye" id="mEyeIcon" style="width: 13px; height: 13px;"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyInputVal('mPassword')">
                                        <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Display Name -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('Caller Display Name') }}</label>
                                <input type="text" class="form-control form-control-sm" id="mDisplayName" readonly>
                            </div>

                            <!-- SIP Port -->
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('SIP Port') }}</label>
                                <input type="text" class="form-control form-control-sm font-monospace" value="5060" readonly>
                            </div>

                            <!-- Transport Protocol -->
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('Transport') }}</label>
                                <input type="text" class="form-control form-control-sm font-monospace" value="UDP / TCP" readonly>
                            </div>

                            <!-- Outbound Proxy -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">{{ __('Outbound Proxy (Optional)') }}</label>
                                <input type="text" class="form-control form-control-sm font-monospace" value="{{ config('pbx.domain', request()->getHost()) }}:5060" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setup Instructions Accordion -->
                <div class="accordion" id="phoneGuidesAccordion">
                    <!-- Grandstream & Yealink Hardware Phones -->
                    <div class="accordion-item border rounded-3 mb-2">
                        <h2 class="accordion-header" id="headingHardware">
                            <button class="accordion-button collapsed py-2.5 px-3 fw-bold small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHardware" aria-expanded="false" aria-controls="collapseHardware">
                                <i data-lucide="phone" class="me-2 text-primary" style="width: 16px; height: 16px;"></i>
                                {{ __('Hardware IP Deskphone Setup (Yealink, Grandstream, Fanvil, Cisco)') }}
                            </button>
                        </h2>
                        <div id="collapseHardware" class="accordion-collapse collapse" aria-labelledby="headingHardware" data-bs-parent="#phoneGuidesAccordion">
                            <div class="accordion-body small text-muted p-3">
                                <ol class="mb-0 ps-3">
                                    <li class="mb-1"><strong>{{ __('Open Web UI:') }}</strong> {{ __('Find the phone IP address from its screen menu and open it in your browser (e.g. http://192.168.1.150).') }}</li>
                                    <li class="mb-1"><strong>{{ __('Go to Account Settings:') }}</strong> {{ __('Navigate to Account ➔ Account 1 ➔ General Settings.') }}</li>
                                    <li class="mb-1"><strong>{{ __('Enter SIP Details:') }}</strong>
                                        <ul class="ps-3 mt-1">
                                            <li>{{ __('Account Name / Display Name:') }} <span class="fw-semibold text-dark">{{ __('Your Name') }}</span></li>
                                            <li>{{ __('SIP Server:') }} <code class="text-primary">{{ config('pbx.domain', request()->getHost()) }}</code></li>
                                            <li>{{ __('SIP User ID / Authenticate ID:') }} <span class="fw-bold text-dark modalExtFill"></span></li>
                                            <li>{{ __('Authenticate Password:') }} <span class="fw-bold text-dark">{{ __('(Your SIP Secret above)') }}</span></li>
                                            <li>{{ __('SIP Transport:') }} <span class="badge bg-light text-dark border">UDP</span></li>
                                        </ul>
                                    </li>
                                    <li><strong>{{ __('Save & Apply:') }}</strong> {{ __('Click Save/Apply. The status on your phone screen will change to "Registered" or "SIP OK".') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Software Softphones (Zoiper, MicroSIP) -->
                    <div class="accordion-item border rounded-3">
                        <h2 class="accordion-header" id="headingSoftphone">
                            <button class="accordion-button collapsed py-2.5 px-3 fw-bold small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSoftphone" aria-expanded="false" aria-controls="collapseSoftphone">
                                <i data-lucide="laptop" class="me-2 text-info" style="width: 16px; height: 16px;"></i>
                                {{ __('Mobile & Desktop Softphone Setup (Zoiper, MicroSIP, GS Wave)') }}
                            </button>
                        </h2>
                        <div id="collapseSoftphone" class="accordion-collapse collapse" aria-labelledby="headingSoftphone" data-bs-parent="#phoneGuidesAccordion">
                            <div class="accordion-body small text-muted p-3">
                                <ol class="mb-0 ps-3">
                                    <li class="mb-1"><strong>{{ __('Install App:') }}</strong> {{ __('Download Zoiper or GS Wave on Android/iOS, or MicroSIP on Windows.') }}</li>
                                    <li class="mb-1"><strong>{{ __('Add Account:') }}</strong> {{ __('Choose "SIP Account" and enter:') }}
                                        <ul class="ps-3 mt-1">
                                            <li>{{ __('Domain / Host / PBX:') }} <code class="text-primary">{{ config('pbx.domain', request()->getHost()) }}</code></li>
                                            <li>{{ __('Username / Login / User:') }} <span class="fw-bold text-dark modalExtFill"></span></li>
                                            <li>{{ __('Password / Secret:') }} <span class="fw-bold text-dark">{{ __('(Your SIP Secret above)') }}</span></li>
                                            <li>{{ __('Caller ID / Name:') }} <span class="fw-bold text-dark modalNameFill"></span></li>
                                        </ul>
                                    </li>
                                    <li><strong>{{ __('Register:') }}</strong> {{ __('Click Register. When the status icon turns green, your phone is live and ready for HD calls!') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function openDeviceSetupModal(ext, name, pass, userName) {
        document.getElementById('mExtHeaderBadge').innerText = ext;
        document.getElementById('mExtension').value = ext;
        document.getElementById('mPassword').value = pass;
        document.getElementById('mDisplayName').value = name + (userName ? ' (' + userName + ')' : '');
        
        document.querySelectorAll('.modalExtFill').forEach(function(el) {
            el.innerText = ext;
        });
        document.querySelectorAll('.modalNameFill').forEach(function(el) {
            el.innerText = name;
        });

        // Reset password visibility
        const passInput = document.getElementById('mPassword');
        passInput.type = 'password';
        const eyeIcon = document.getElementById('mEyeIcon');
        if (eyeIcon) eyeIcon.setAttribute('data-lucide', 'eye');
        if (window.lucide) lucide.createIcons();

        const modalEl = document.getElementById('deviceSetupModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function toggleModalPass() {
        const pass = document.getElementById('mPassword');
        const icon = document.getElementById('mEyeIcon');
        if (pass.type === 'password') {
            pass.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            pass.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide) lucide.createIcons();
    }

    function copyInputVal(elementId) {
        const el = document.getElementById(elementId);
        if (!el) return;
        navigator.clipboard.writeText(el.value).then(function() {
            if (typeof window.showToast === 'function') {
                window.showToast('success', 'Copied to clipboard: ' + el.value);
            } else {
                alert('Copied: ' + el.value);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
