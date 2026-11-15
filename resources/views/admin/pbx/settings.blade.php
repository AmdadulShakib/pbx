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
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
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
                                    <div class="form-check card p-3 border {{ $driver === 'asterisk' ? 'border-primary bg-primary-subtle' : 'border-secondary-subtle' }}">
                                        <input class="form-check-input" type="radio" name="driver" id="driver_asterisk" value="asterisk" {{ $driver === 'asterisk' ? 'checked' : '' }} onchange="updateDefaultWsUrl()">
                                        <label class="form-check-label fw-bold ms-2" for="driver_asterisk">
                                            Asterisk (PJSIP + WebRTC)
                                            <div class="text-muted small fw-normal mt-1">{{ __('Default WebRTC WS on port 8088 (/ws), robust SIP engine.') }}</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check card p-3 border {{ $driver === 'freeswitch' ? 'border-primary bg-primary-subtle' : 'border-secondary-subtle' }}">
                                        <input class="form-check-input" type="radio" name="driver" id="driver_freeswitch" value="freeswitch" {{ $driver === 'freeswitch' ? 'checked' : '' }} onchange="updateDefaultWsUrl()">
                                        <label class="form-check-label fw-bold ms-2" for="driver_freeswitch">
                                            FreeSWITCH (mod_sofia)
                                            <div class="text-muted small fw-normal mt-1">{{ __('Event Socket (ESL 8021) with WebRTC WS on port 5066 / 7443.') }}</div>
                                        </label>
                                    </div>
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

        <!-- Quick Help & Documentation Card -->
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-dark">
                    <i data-lucide="help-circle" class="me-2 text-primary" style="width: 18px; height: 18px;"></i>
                    {{ __('Asterisk WebRTC Checklist') }}
                </h6>
                <div class="row g-3 small text-muted">
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-white rounded border">
                            <strong class="text-dark d-block mb-1">1. HTTP WebSocket (8088)</strong>
                            Asterisk এর <code>/etc/asterisk/http.conf</code> ফাইলে <code>bindport=8088</code> এবং <code>enabled=yes</code> থাকতে হবে।
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-white rounded border">
                            <strong class="text-dark d-block mb-1">2. DTLS Encryption</strong>
                            WebRTC কলিংয়ের অডিও এনক্রিপশনের জন্য <code>pjsip.conf</code> এ <code>dtls_cert_file</code> এবং <code>dtls_private_key</code> সেট থাকতে হবে।
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
