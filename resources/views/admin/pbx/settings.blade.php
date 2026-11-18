@extends('admin.layouts.app')
@section('title', __('PBX Server Settings'))
@section('page-title', __('PBX Server Settings'))

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        @if(session('ok'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i data-lucide="check-circle" class="me-2"></i>{{ session('ok') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-bold mb-1"><i data-lucide="alert-circle" class="me-2"></i>{{ __('Please correct the errors below:') }}</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0 fw-bold">
                        <i data-lucide="server" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
                        {{ __('PBX Core Configuration') }}
                    </h5>
                    <small class="text-muted">{{ __('Configure Asterisk / FreeSWITCH server connection and WebRTC WebSocket endpoints') }}</small>
                </div>
                <a href="{{ route('admin.pbx.extensions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i data-lucide="arrow-left" class="me-1" style="width: 14px; height: 14px;"></i>
                    {{ __('Back to Extensions') }}
                </a>
            </div>

            <form action="{{ route('admin.pbx.settings.save') }}" method="POST">
                @csrf
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Server Type / Driver -->
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('PBX Engine / Backend Server') }} <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="engine-card d-block p-3 rounded-3 border cursor-pointer transition {{ $driver === 'asterisk' ? 'border-primary bg-primary-subtle shadow-sm' : 'border-light-subtle bg-body' }}" for="driver_asterisk" style="cursor: pointer;">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1" type="radio" name="driver" id="driver_asterisk" value="asterisk" {{ $driver === 'asterisk' ? 'checked' : '' }} onchange="updateEngineCardSelection(); updateDefaultWsUrl();">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-dark fs-6">Asterisk (PJSIP + WebRTC)</span>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-2 font-monospace" style="font-size: 11px;">{{ __('Recommended') }}</span>
                                                </div>
                                                <p class="text-muted small mb-0">{{ __('High-performance PJSIP stack, WebRTC WebSocket proxy on port 8088 (/ws), and full SIP trunking support.') }}</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="engine-card d-block p-3 rounded-3 border cursor-pointer transition {{ $driver === 'freeswitch' ? 'border-primary bg-primary-subtle shadow-sm' : 'border-light-subtle bg-body' }}" for="driver_freeswitch" style="cursor: pointer;">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1" type="radio" name="driver" id="driver_freeswitch" value="freeswitch" {{ $driver === 'freeswitch' ? 'checked' : '' }} onchange="updateEngineCardSelection(); updateDefaultWsUrl();">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-dark fs-6">FreeSWITCH (mod_sofia)</span>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-0.5 px-2 font-monospace" style="font-size: 11px;">ESL 8021</span>
                                                </div>
                                                <p class="text-muted small mb-0">{{ __('Event Socket Library (ESL) interface with WebRTC WebSocket on port 5066 / 7443.') }}</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- PBX Domain / Server IP -->
                        <div class="col-12 col-md-6">
                            <label for="domain" class="form-label fw-semibold">
                                {{ __('PBX Domain / Server IP') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('domain') is-invalid @enderror" 
                                   id="domain" 
                                   name="domain" 
                                   value="{{ old('domain', $domain) }}" 
                                   placeholder="e.g. 192.168.1.100 or pbx.yourdomain.com" 
                                   required 
                                   oninput="syncWsUrlWithDomain()">
                            <small class="text-muted">{{ __('The IP address or domain name where Asterisk / FreeSWITCH is hosted.') }}</small>
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- WebSocket URL -->
                        <div class="col-12 col-md-6">
                            <label for="ws_url" class="form-label fw-semibold">
                                {{ __('WebRTC WebSocket URL') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('ws_url') is-invalid @enderror" 
                                   id="ws_url" 
                                   name="ws_url" 
                                   value="{{ old('ws_url', $ws_url) }}" 
                                   placeholder="e.g. ws://192.168.1.100:8088/ws or wss://pbx.domain.com/ws" 
                                   required>
                            <small class="text-muted">{{ __('Asterisk default: ws://SERVER_IP:8088/ws | FreeSWITCH: ws://SERVER_IP:5066') }}</small>
                            @error('ws_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Admin Emails -->
                        <div class="col-12">
                            <label for="admin_emails" class="form-label fw-semibold">
                                {{ __('PBX Admin Emails') }}
                            </label>
                            <input type="text"
                                   class="form-control @error('admin_emails') is-invalid @enderror"
                                   id="admin_emails"
                                   name="admin_emails"
                                   value="{{ old('admin_emails', $admin_emails) }}"
                                   placeholder="admin@yourdomain.com, manager@yourdomain.com">
                            <small class="text-muted">{{ __('Comma-separated list of admin email addresses who have PBX access.') }}</small>
                            @error('admin_emails')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center border-top">
                    <span class="text-muted small">
                        <i data-lucide="info" class="me-1" style="width: 14px; height: 14px;"></i>
                        {{ __('Saving automatically updates the application environment configuration.') }}
                    </span>
                    <button type="submit" class="btn btn-primary px-4">
                        <i data-lucide="save" class="me-1" style="width: 16px; height: 16px;"></i>
                        {{ __('Save PBX Settings') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Asterisk AMI Settings Card --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold">
                    <i data-lucide="terminal" class="me-2 text-warning" style="width: 20px; height: 20px;"></i>
                    {{ __('Asterisk AMI / Rawman Connection') }}
                </h5>
                <small class="text-muted">{{ __('These credentials allow the app to control Asterisk via AMI (live calls, originate, hangup). Required for Live Calls panel.') }}</small>
            </div>
            <form action="{{ route('admin.pbx.settings.save') }}" method="POST">
                @csrf
                <input type="hidden" name="_section" value="ami">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="ami_host" class="form-label fw-semibold">{{ __('AMI Host') }}</label>
                            <input type="text"
                                   class="form-control @error('ami_host') is-invalid @enderror"
                                   id="ami_host" name="ami_host"
                                   value="{{ old('ami_host', $ami_host) }}"
                                   placeholder="127.0.0.1">
                            <small class="text-muted">{{ __('Usually 127.0.0.1 (localhost) if Asterisk is on the same server.') }}</small>
                            @error('ami_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="ami_port" class="form-label fw-semibold">{{ __('AMI Port') }}</label>
                            <input type="number"
                                   class="form-control @error('ami_port') is-invalid @enderror"
                                   id="ami_port" name="ami_port"
                                   value="{{ old('ami_port', $ami_port) }}"
                                   placeholder="5038">
                            <small class="text-muted">{{ __('Default Asterisk AMI port is 5038.') }}</small>
                            @error('ami_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="ami_user" class="form-label fw-semibold">{{ __('AMI Username') }}</label>
                            <input type="text"
                                   class="form-control @error('ami_user') is-invalid @enderror"
                                   id="ami_user" name="ami_user"
                                   value="{{ old('ami_user', $ami_user) }}"
                                   placeholder="admin">
                            <small class="text-muted">{{ __('Defined in /etc/asterisk/manager.conf') }}</small>
                            @error('ami_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="ami_secret" class="form-label fw-semibold">{{ __('AMI Secret / Password') }}</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('ami_secret') is-invalid @enderror"
                                       id="ami_secret" name="ami_secret"
                                       value="{{ old('ami_secret', $ami_secret) }}"
                                       placeholder="••••••••">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                                    <i data-lucide="eye" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                            <small class="text-muted">{{ __('Secret key set in manager.conf for this AMI user.') }}</small>
                            @error('ami_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="rawman_url" class="form-label fw-semibold">{{ __('Rawman / HTTP AMI URL') }}</label>
                            <input type="text"
                                   class="form-control @error('rawman_url') is-invalid @enderror"
                                   id="rawman_url" name="rawman_url"
                                   value="{{ old('rawman_url', $rawman_url) }}"
                                   placeholder="https://pbx.yourdomain.com/rawman">
                            <small class="text-muted">{{ __('Full URL to Asterisk HTTP Manager (rawman). Used for live call monitoring. Must be accessible from this app server.') }}</small>
                            @error('rawman_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center border-top">
                    <span class="text-muted small">
                        <i data-lucide="info" class="me-1" style="width: 14px; height: 14px;"></i>
                        {{ __('Saving automatically updates the application environment configuration.') }}
                    </span>
                    <button type="submit" class="btn btn-primary px-4">
                        <i data-lucide="save" class="me-1" style="width: 16px; height: 16px;"></i>
                        {{ __('Save AMI Settings') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Help & Documentation Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i data-lucide="check-square" class="text-success" style="width: 18px; height: 18px;"></i>
                        <span>{{ __('Asterisk WebRTC & SIP Configuration Checklist') }}</span>
                    </h6>
                    <small class="text-muted">{{ __('Verify essential server files and network firewall ports for audio calls') }}</small>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded border bg-light-subtle h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2 fw-semibold">Port 8088</span>
                                <strong class="text-dark small">{{ __('WebRTC WebSocket (/ws)') }}</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('Ensure') }} <code>enabled=yes</code> {{ __('and') }} <code>bindport=8088</code> {{ __('are configured in') }} <code>/etc/asterisk/http.conf</code>.
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded border bg-light-subtle h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 fw-semibold">DTLS-SRTP</span>
                                <strong class="text-dark small">{{ __('Audio Media Encryption') }}</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('WebRTC browser calls require DTLS keys. Verify') }} <code>dtls_cert_file</code> {{ __('in') }} <code>/etc/asterisk/pjsip.conf</code>.
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded border bg-light-subtle h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-info-subtle text-info border border-info-subtle py-1 px-2 fw-semibold">Port 5060</span>
                                <strong class="text-dark small">{{ __('Hardware SIP IP Phones') }}</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('Deskphones (Grandstream, Yealink) register via standard SIP UDP/TCP on port 5060.') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded border bg-light-subtle h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1 px-2 fw-semibold">10000–20000</span>
                                <strong class="text-dark small">{{ __('RTP Voice Media Ports') }}</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('Firewall must permit inbound UDP 10000–20000 for bidirectional voice audio packets.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function updateEngineCardSelection() {
        const isAsterisk = document.getElementById('driver_asterisk').checked;
        const asteriskCard = document.querySelector('label[for="driver_asterisk"]');
        const freeswitchCard = document.querySelector('label[for="driver_freeswitch"]');
        
        if (isAsterisk) {
            asteriskCard.classList.add('border-primary', 'bg-primary-subtle', 'shadow-sm');
            asteriskCard.classList.remove('border-light-subtle', 'bg-body');
            freeswitchCard.classList.remove('border-primary', 'bg-primary-subtle', 'shadow-sm');
            freeswitchCard.classList.add('border-light-subtle', 'bg-body');
        } else {
            freeswitchCard.classList.add('border-primary', 'bg-primary-subtle', 'shadow-sm');
            freeswitchCard.classList.remove('border-light-subtle', 'bg-body');
            asteriskCard.classList.remove('border-primary', 'bg-primary-subtle', 'shadow-sm');
            asteriskCard.classList.add('border-light-subtle', 'bg-body');
        }
    }
    function updateDefaultWsUrl() {
        const isAsterisk = document.getElementById('driver_asterisk').checked;
        const domain = document.getElementById('domain').value.trim() || '127.0.0.1';
        const wsInput = document.getElementById('ws_url');

        if (isAsterisk) {
            wsInput.value = 'ws://' + domain + ':8088/ws';
        } else {
            wsInput.value = 'ws://' + domain + ':5066';
        }
    }

    function syncWsUrlWithDomain() {
        const domain = document.getElementById('domain').value.trim();
        if (!domain) return;
        const isAsterisk = document.getElementById('driver_asterisk').checked;
        const wsInput = document.getElementById('ws_url');
        
        // Only update if standard ws pattern
        if (wsInput.value.startsWith('ws://')) {
            if (isAsterisk) {
                wsInput.value = 'ws://' + domain + ':8088/ws';
            } else {
                wsInput.value = 'ws://' + domain + ':5066';
            }
        }
    }
</script>
@endpush
