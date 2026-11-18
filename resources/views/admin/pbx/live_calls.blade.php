@extends('admin.layouts.app')
@section('title', __('Live Active Calls & Channel Monitor'))
@section('page-title', __('Live Active Calls'))

@section('content')
<div class="row g-3">
    <!-- Top Action Deck -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger-subtle text-danger p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="radio" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold text-dark">{{ __('Real-Time Active Channel Monitor') }}</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2 py-0.5">
                                <span class="spinner-grow spinner-grow-sm" style="width: 7px; height: 7px;" role="status"></span>
                                <span>{{ __('Live Polling (3s)') }}</span>
                            </span>
                        </div>
                        <p class="text-muted small mb-0">{{ __('Inspect active RTP voice channels, silently listen, coach agents with whisper, or 3-way barge.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2 me-md-2">
                        <label class="small text-muted mb-0 fw-semibold text-nowrap">{{ __('Supervisor Ext:') }}</label>
                        <select id="supervisor-ext-select" class="form-select form-select-sm" style="width: 140px;">
                            @foreach($extensions as $ext)
                                <option value="{{ $ext->extension }}" @selected($ext->extension == $currentAdminExt)>
                                    Ext {{ $ext->extension }} ({{ Str::limit($ext->name, 10) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" onclick="fetchLiveChannels();">
                        <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Refresh Now') }}</span>
                    </button>
                    <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();">
                        <i data-lucide="phone-call" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Launch Softphone') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Channels Table -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Active Voice Channels') }}</h6>
                    <span id="live-channel-count-badge" class="badge bg-primary rounded-pill px-2.5 py-1">
                        {{ count($channels) }} {{ __('Active') }}
                    </span>
                </div>
                <span class="text-muted small" id="last-updated-text">{{ __('Updated just now') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="live-channels-table">
                    <thead class="table-custom-head text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 ps-md-4 py-3">{{ __('Caller') }}</th>
                            <th class="py-3">{{ __('Destination') }}</th>
                            <th class="py-3">{{ __('State') }}</th>
                            <th class="py-3">{{ __('Talk Time') }}</th>
                            <th class="py-3">{{ __('Channel') }}</th>
                            <th class="text-end pe-3 pe-md-4 py-3">{{ __('Supervisor Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="live-channels-tbody">
                        @forelse($channels as $chan)
                            <tr data-channel="{{ $chan['channel'] }}">
                                <td class="ps-3 ps-md-4 py-3">
                                    <div class="d-flex align-items-center gap-2.5">
                                        @if(!empty($chan['caller_avatar']))
                                            <img src="{{ $chan['caller_avatar'] }}" class="rounded-circle object-fit-cover shadow-sm flex-shrink-0" style="width: 36px; height: 36px; border: 2px solid #e2e8f0;" alt="{{ $chan['caller_name'] ?? 'Caller' }}">
                                        @else
                                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px;">
                                                {{ substr($chan['caller'], 0, 3) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark font-monospace">
                                                Ext {{ $chan['caller'] }}
                                                @if(!empty($chan['caller_name']))
                                                    <span class="fw-semibold text-secondary small ms-1">({{ $chan['caller_name'] }})</span>
                                                @endif
                                            </div>
                                            <small class="text-muted d-block" style="font-size: 11px;">{{ $chan['app'] ?: 'Voice' }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3">
                                    <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-pill px-2.5 py-1">
                                        @if(!empty($chan['callee_avatar']))
                                            <img src="{{ $chan['callee_avatar'] }}" class="rounded-circle object-fit-cover shadow-sm flex-shrink-0" style="width: 22px; height: 22px; border: 1.5px solid #cbd5e1;" alt="{{ $chan['callee_name'] ?? 'Callee' }}">
                                        @endif
                                        <span class="font-monospace small fw-bold text-dark">
                                            {{ str_starts_with($chan['callee'], 'Ext') ? $chan['callee'] : 'Ext ' . $chan['callee'] }}
                                        </span>
                                        @if(!empty($chan['callee_name']))
                                            <span class="fw-normal text-muted small">({{ $chan['callee_name'] }})</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2.5 py-1">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: var(--bs-success, #16a34a);"></span>
                                        <span>{{ $chan['state'] }}</span>
                                    </span>
                                </td>

                                <td class="py-3 font-monospace fw-medium text-dark">
                                    {{ $chan['formatted_duration'] }}
                                </td>

                                <td class="py-3 font-monospace small text-muted text-truncate" style="max-width: 130px;" title="{{ $chan['channel'] }}">
                                    {{ $chan['channel'] }}
                                </td>

                                <td class="text-end pe-3 pe-md-4 py-3">
                                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary btn-action-icon" 
                                                onclick="startSpy('{{ $chan['channel'] }}', '{{ $chan['caller'] }}', 'listen')" 
                                                title="{{ __('Listen Silently (*55)') }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="headphones"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning btn-action-icon" 
                                                onclick="startSpy('{{ $chan['channel'] }}', '{{ $chan['caller'] }}', 'whisper')" 
                                                title="{{ __('Whisper Coach (*56)') }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="mic"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success btn-action-icon" 
                                                onclick="startSpy('{{ $chan['channel'] }}', '{{ $chan['caller'] }}', 'barge')" 
                                                title="{{ __('Barge In 3-Way (*57)') }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="users"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger btn-action-icon" 
                                                onclick="hangupChannel('{{ $chan['channel'] }}');" 
                                                title="{{ __('Force Terminate Call') }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="phone-off"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-channels-row">
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="rounded-circle bg-light border p-3 mb-3 text-muted">
                                            <i data-lucide="radio" style="width: 28px; height: 28px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('No Live Calls Currently Active') }}</h6>
                                        <p class="text-muted small mb-0">{{ __('When employees make internal or external calls, active bridges will appear here in real-time.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Supervisor Guide & Bridge Form -->
    <div class="col-12 col-xl-4">
        <!-- Call Monitoring Feature Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 bg-warning-subtle text-warning p-1.5 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i data-lucide="shield" style="width: 15px; height: 15px;"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Supervisor Call Coaching') }}</h6>
                </div>
            </div>
            <div class="card-body p-3 p-md-4 pt-0">
                <p class="text-muted small mb-3">{{ __('Monitor active calls in real time. Click any action button above, and Asterisk will instantly call your softphone to bridge you into the call.') }}</p>

                <div class="list-group list-group-flush border rounded-3 mb-3 small">
                    <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-0 bg-transparent">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1.5 px-2 py-1">
                                <i data-lucide="headphones" style="width: 12px; height: 12px;"></i>
                                <span>{{ __('Silent Monitor') }}</span>
                            </span>
                        </div>
                        <span class="font-monospace small text-muted bg-light px-2 py-0.5 rounded border">*55&lt;ext&gt;</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-0 bg-transparent">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle d-inline-flex align-items-center gap-1.5 px-2 py-1">
                                <i data-lucide="mic" style="width: 12px; height: 12px;"></i>
                                <span>{{ __('Coach Agent Only') }}</span>
                            </span>
                        </div>
                        <span class="font-monospace small text-muted bg-light px-2 py-0.5 rounded border">*56&lt;ext&gt;</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-0 bg-transparent">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2 py-1">
                                <i data-lucide="users" style="width: 12px; height: 12px;"></i>
                                <span>{{ __('Join 3-Way Call') }}</span>
                            </span>
                        </div>
                        <span class="font-monospace small text-muted bg-light px-2 py-0.5 rounded border">*57&lt;ext&gt;</span>
                    </div>
                </div>

                <div class="p-3 rounded-3 border" style="background: rgba(37, 99, 235, 0.04); border-color: rgba(37, 99, 235, 0.15) !important;">
                    <div class="fw-bold small text-primary mb-1 d-flex align-items-center gap-1.5">
                        <i data-lucide="info" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('In-Call DTMF Mode Switching') }}</span>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 12px; line-height: 1.5;">
                        {{ __('While monitoring, press') }} 
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-1.5 py-0.5">4</span> {{ __('for Listen,') }} 
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-1.5 py-0.5">5</span> {{ __('for Whisper, or') }} 
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1.5 py-0.5">6</span> {{ __('for Barge on your keypad.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Server Call Bridge -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 bg-primary-subtle text-primary p-1.5 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i data-lucide="phone-forwarded" style="width: 15px; height: 15px;"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Server Call Bridge') }}</h6>
                </div>
            </div>
            <div class="card-body p-3 p-md-4 pt-0">
                <p class="text-muted small">{{ __('Originate a server-side call bridge between any two extensions.') }}</p>

                <form method="POST" action="{{ route('admin.pbx.live-calls.originate') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">{{ __('From Extension') }}</label>
                        <select name="from" class="form-select form-select-sm" required>
                            @foreach($extensions as $ext)
                                <option value="{{ $ext->extension }}">
                                    Ext {{ $ext->extension }} - {{ $ext->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">{{ __('To Extension / Number') }}</label>
                        <input type="text" name="to" class="form-control form-control-sm" placeholder="e.g. 102 or 999" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1.5">
                        <i data-lucide="phone-outgoing" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Bridge Call Now') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    let pollTimer = null;

    function getSelectedSupervisorExt() {
        const sel = document.getElementById('supervisor-ext-select');
        return sel ? sel.value : '{{ $currentAdminExt }}';
    }

    async function fetchLiveChannels() {
        try {
            const res = await fetch("{{ route('admin.pbx.live-calls.api') }}", {
                headers: { 'Accept': 'application/json' }
            });
            if (res.status === 401) {
                const countBadge = document.getElementById('live-channel-count-badge');
                if (countBadge) {
                    countBadge.className = 'badge bg-warning text-dark rounded-pill px-2.5 py-1';
                    countBadge.textContent = '{{ __("Session Paused") }}';
                }
                const lastUpdated = document.getElementById('last-updated-text');
                if (lastUpdated) {
                    lastUpdated.innerHTML = '<span class="text-warning small"><i data-lucide="alert-circle" style="width:12px;height:12px;" class="me-1"></i>{{ __("Admin session expired. Please refresh to reconnect.") }}</span>';
                }
                return;
            }
            if (!res.ok) return;
            const data = await res.json();
            renderLiveChannels(data.channels || []);
            document.getElementById('last-updated-text').textContent = 'Updated ' + new Date().toLocaleTimeString();
        } catch (e) {
            console.warn('[PBX Monitor] Polling error:', e);
        }
    }

    function renderLiveChannels(channels) {
        const countBadge = document.getElementById('live-channel-count-badge');
        countBadge.className = 'badge bg-primary rounded-pill px-2.5 py-1';
        countBadge.textContent = channels.length + ' {{ __("Active") }}';

        const tbody = document.getElementById('live-channels-tbody');
        if (channels.length === 0) {
            tbody.innerHTML = `
                <tr id="empty-channels-row">
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="rounded-circle bg-light border p-3 mb-3 text-muted">
                                <i data-lucide="radio" style="width: 28px; height: 28px;"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ __('No Live Calls Currently Active') }}</h6>
                            <p class="text-muted small mb-0">{{ __('When employees make internal or external calls, active bridges will appear here in real-time.') }}</p>
                        </div>
                    </td>
                </tr>
            `;
            if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            return;
        }

        let html = '';
        channels.forEach(chan => {
            const callerAvatarHtml = chan.caller_avatar
                ? `<img src="${chan.caller_avatar}" class="rounded-circle object-fit-cover shadow-sm flex-shrink-0" style="width: 36px; height: 36px; border: 2px solid #e2e8f0;" alt="${chan.caller_name || ''}">`
                : `<div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px;">${chan.caller.substring(0, 3)}</div>`;

            const calleeAvatarHtml = chan.callee_avatar
                ? `<img src="${chan.callee_avatar}" class="rounded-circle object-fit-cover shadow-sm flex-shrink-0" style="width: 22px; height: 22px; border: 1.5px solid #cbd5e1;" alt="${chan.callee_name || ''}">`
                : '';

            const callerNameHtml = chan.caller_name ? `<span class="fw-semibold text-secondary small ms-1">(${chan.caller_name})</span>` : '';
            const calleeDisplay = chan.callee.toString().startsWith('Ext') ? chan.callee : 'Ext ' + chan.callee;
            const calleeNameHtml = chan.callee_name ? ` <span class="fw-normal text-muted small">(${chan.callee_name})</span>` : '';

            html += `
                <tr data-channel="${chan.channel}">
                    <td class="ps-3 ps-md-4 py-3">
                        <div class="d-flex align-items-center gap-2.5">
                            ${callerAvatarHtml}
                            <div>
                                <div class="fw-bold text-dark font-monospace">Ext ${chan.caller}${callerNameHtml}</div>
                                <small class="text-muted d-block" style="font-size: 11px;">${chan.app || 'Voice'}</small>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-pill px-2.5 py-1">
                            ${calleeAvatarHtml}
                            <span class="font-monospace small fw-bold text-dark">${calleeDisplay}</span>
                            ${calleeNameHtml}
                        </div>
                    </td>
                    <td class="py-3">
                        <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2.5 py-1">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background-color: var(--bs-success, #16a34a);"></span>
                            <span>${chan.state}</span>
                        </span>
                    </td>
                    <td class="py-3 font-monospace fw-medium text-dark">
                        ${chan.formatted_duration}
                    </td>
                    <td class="py-3 font-monospace small text-muted text-truncate" style="max-width: 130px;" title="${chan.channel}">
                        ${chan.channel}
                    </td>
                    <td class="text-end pe-3 pe-md-4 py-3">
                        <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-action-icon" onclick="startSpy('${chan.channel}', '${chan.caller}', 'listen')" title="{{ __('Listen Silently (*55)') }}" data-bs-toggle="tooltip">
                                <i data-lucide="headphones"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning btn-action-icon" onclick="startSpy('${chan.channel}', '${chan.caller}', 'whisper')" title="{{ __('Whisper Coach (*56)') }}" data-bs-toggle="tooltip">
                                <i data-lucide="mic"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success btn-action-icon" onclick="startSpy('${chan.channel}', '${chan.caller}', 'barge')" title="{{ __('Barge In 3-Way (*57)') }}" data-bs-toggle="tooltip">
                                <i data-lucide="users"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon" onclick="hangupChannel('${chan.channel}');" title="{{ __('Force Terminate') }}" data-bs-toggle="tooltip">
                                <i data-lucide="phone-off"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    }

    async function startSpy(channel, ext, mode) {
        const supervisorExt = getSelectedSupervisorExt();
        const modeMap = {
            'listen': { code: '*55', label: '{{ __("Silent Listen") }}' },
            'whisper': { code: '*56', label: '{{ __("Whisper Coaching") }}' },
            'barge': { code: '*57', label: '{{ __("3-Way Conference Barge") }}' }
        };

        const targetExt = (ext && ext !== 'Unknown') ? ext : channel.replace(/[^0-9]/g, '').substring(0, 3);
        const actionInfo = modeMap[mode] || { code: '*55', label: mode };
        const dialNumber = actionInfo.code + targetExt;
        const callLabel = `${actionInfo.label} (Ext ${targetExt})`;

        // Direct browser softphone join: connect directly into the call without incoming ring!
        if (typeof window.pbxMakeCall === 'function') {
            if (typeof togglePbxDialer === 'function') {
                const card = document.getElementById('pbx-dialer-card');
                if (card && !card.classList.contains('active')) {
                    togglePbxDialer();
                }
            }
            window.pbxMakeCall(dialNumber, callLabel);
            return;
        }

        // Fallback: If web softphone is not initialized, fallback to server AMI Originate
        const confirmMsg = `${actionInfo.label} on Ext ${targetExt}?\n\nAsterisk will ring your softphone (Ext ${supervisorExt}). Answer the softphone to connect.`;
        if (!confirm(confirmMsg)) return;

        try {
            const res = await fetch("{{ route('admin.pbx.live-calls.spy') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    admin_extension: supervisorExt,
                    target: channel || targetExt,
                    mode: mode
                })
            });

            const data = await res.json();
            if (data.success) {
                if (typeof togglePbxDialer === 'function') {
                    const card = document.getElementById('pbx-dialer-card');
                    if (card && !card.classList.contains('active')) {
                        togglePbxDialer();
                    }
                }
                alert(data.message || 'Call initiated. Please answer your softphone!');
            } else {
                alert(data.message || 'Failed to start monitoring session.');
            }
        } catch (e) {
            console.error('[PBX Spy] Error:', e);
            alert('Error initiating supervisor call.');
        }
    }

    async function hangupChannel(channel) {
        if (!confirm('{{ __("Are you sure you want to forcibly terminate this call?") }}')) return;

        try {
            const res = await fetch("{{ route('admin.pbx.live-calls.hangup') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ channel: channel })
            });
            const data = await res.json();
            if (data.success) {
                fetchLiveChannels();
            } else {
                alert(data.message || 'Failed to hangup channel');
            }
        } catch (e) {
            alert('Error hanging up channel');
        }
    }

    // Auto-poll live calls every 3.5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        pollTimer = setInterval(fetchLiveChannels, 3500);
    });

    window.addEventListener('beforeunload', function () {
        if (pollTimer) clearInterval(pollTimer);
    });
</script>
@endpush
