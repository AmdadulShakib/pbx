@extends('admin.layouts.app')
@section('title', __('SIP Trunks & Carrier Gateways'))
@section('page-title', __('SIP Trunks'))

@section('content')
<div class="row g-3">
    <!-- Top Action Header -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="network" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('SIP Carrier Trunks') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Connect your PBX to VoIP carriers, telecom providers, Twilio, Telnyx, or PSTN gateways.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.pbx.trunks.sync') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" title="{{ __('Compile & sync trunk configurations to Asterisk') }}">
                            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                            <span>{{ __('Sync to Asterisk') }}</span>
                        </button>
                    </form>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createTrunkModal">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Add SIP Trunk') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('trunks_snippet'))
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-dark mb-0">{{ __('Compiled Asterisk PJSIP Trunk Configuration') }}</h6>
                        <span class="badge bg-success-subtle text-success">{{ __('PJSIP Reloaded') }}</span>
                    </div>
                    <pre class="bg-dark text-light p-3 rounded-3 small font-monospace mb-0" style="max-height: 180px; overflow-y: auto;">{{ session('trunks_snippet') }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- Trunks Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">{{ __('Configured Carrier Trunks') }}</h6>
                <span class="badge bg-primary rounded-pill px-2.5 py-1">{{ count($trunks) }} {{ __('Total') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-custom-head text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 ps-md-4 py-3">{{ __('Trunk Name') }}</th>
                            <th class="py-3">{{ __('Host / Server') }}</th>
                            <th class="py-3">{{ __('Transport / Type') }}</th>
                            <th class="py-3">{{ __('Outbound Caller ID') }}</th>
                            <th class="py-3">{{ __('Channels') }}</th>
                            <th class="py-3">{{ __('Status') }}</th>
                            <th class="text-end pe-3 pe-md-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trunks as $trunk)
                            <tr>
                                <td class="ps-3 ps-md-4 py-3">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $trunk->name }}</div>
                                        <small class="text-muted font-monospace">trunk_{{ preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($trunk->name)) }}</small>
                                    </div>
                                </td>

                                <td class="py-3 font-monospace small">
                                    <div>{{ $trunk->host }}:{{ $trunk->port }}</div>
                                    @if($trunk->username)
                                        <div class="text-muted" style="font-size: 11px;">User: {{ $trunk->username }}</div>
                                    @endif
                                </td>

                                <td class="py-3">
                                    <span class="badge bg-light text-dark border text-uppercase px-2 py-1 font-monospace">
                                        {{ $trunk->transport }} / {{ str_replace('pjsip_', '', $trunk->type) }}
                                    </span>
                                </td>

                                <td class="py-3 font-monospace small">
                                    @if($trunk->outbound_caller_id)
                                        <span class="text-dark fw-semibold">{{ $trunk->outbound_caller_id }}</span>
                                    @else
                                        <span class="text-muted">{{ __('System Default') }}</span>
                                    @endif
                                </td>

                                <td class="py-3">
                                    <span class="badge bg-light text-secondary border px-2 py-0.5 font-monospace">
                                        {{ $trunk->max_channels }} {{ __('Max') }}
                                    </span>
                                </td>

                                <td class="py-3">
                                    @if($trunk->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2.5 py-1">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background-color: var(--bs-success, #16a34a);"></span>
                                            <span>{{ __('Active') }}</span>
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0.5">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end pe-3 pe-md-4 py-3">
                                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary btn-action-icon" 
                                                onclick="openEditTrunkModal({{ json_encode($trunk) }})" 
                                                title="{{ __('Edit SIP Trunk') }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.pbx.trunks.destroy', $trunk->id) }}" class="d-inline m-0" onsubmit="return confirm('{{ __('Are you sure you want to delete this trunk?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger btn-action-icon" 
                                                    title="{{ __('Delete SIP Trunk') }}" 
                                                    data-bs-toggle="tooltip">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="rounded-circle bg-light border p-3 mb-3 text-muted">
                                            <i data-lucide="network" style="width: 28px; height: 28px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('No SIP Trunks Configured') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('Add your telecom provider SIP credentials or IP trunk to route calls externally.') }}</p>
                                        <button type="button" class="btn btn-primary btn-sm px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createTrunkModal">
                                            {{ __('Add First Trunk') }}
                                        </button>
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

<!-- Create Trunk Modal -->
<div class="modal fade" id="createTrunkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.pbx.trunks.store') }}">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Add SIP Carrier Trunk') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Trunk Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Twilio US, BTCL Primary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Trunk Type') }} <span class="text-danger">*</span></label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="pjsip_registration">{{ __('PJSIP Registration (Username/Password)') }}</option>
                                <option value="pjsip_ip">{{ __('PJSIP IP Authentication (Static IP)') }}</option>
                                <option value="sip">{{ __('Standard SIP') }}</option>
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('SIP Host / Server / FQDN') }} <span class="text-danger">*</span></label>
                            <input type="text" name="host" class="form-control form-control-sm font-monospace" placeholder="e.g. sip.twilio.com or 192.168.1.100" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Port') }}</label>
                            <input type="number" name="port" class="form-control form-control-sm font-monospace" value="5060" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Transport') }}</label>
                            <select name="transport" class="form-select form-select-sm">
                                <option value="udp">UDP</option>
                                <option value="tcp">TCP</option>
                                <option value="tls">TLS (Secure)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('SIP Auth Username / Account') }}</label>
                            <input type="text" name="username" class="form-control form-control-sm font-monospace" placeholder="e.g. AC123456 or sip_trunk_01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('SIP Auth Password / Secret') }}</label>
                            <input type="password" name="secret" class="form-control form-control-sm font-monospace" placeholder="Carrier SIP Secret">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Outbound Caller ID (DID)') }}</label>
                            <input type="text" name="outbound_caller_id" class="form-control form-control-sm font-monospace" placeholder="e.g. +18005550199 or 01700000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Caller Name Display') }}</label>
                            <input type="text" name="caller_name" class="form-control form-control-sm" placeholder="e.g. VoxPulse Corporate">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Max Channels (Concurrency)') }}</label>
                            <input type="number" name="max_channels" class="form-control form-control-sm" value="10" min="1" max="500">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Allowed Codecs') }}</label>
                            <input type="text" name="codecs" class="form-control form-control-sm font-monospace" value="ulaw,alaw,g729,opus">
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Notes / Provider Info') }}</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional notes about carrier contract, billing, or trunk IP whitelist."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_registration" value="1" id="create_trunk_reg" checked>
                                <label class="form-check-label text-dark small" for="create_trunk_reg">
                                    {{ __('Send outbound SIP registration to carrier') }}
                                </label>
                            </div>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_trunk_active" checked>
                                <label class="form-check-label text-dark small" for="create_trunk_active">
                                    {{ __('Activate trunk immediately') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Save Trunk') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Trunk Modal -->
<div class="modal fade" id="editTrunkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editTrunkForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Edit SIP Carrier Trunk') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Trunk Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_trunk_name" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Trunk Type') }} <span class="text-danger">*</span></label>
                            <select id="edit_trunk_type" name="type" class="form-select form-select-sm" required>
                                <option value="pjsip_registration">{{ __('PJSIP Registration (Username/Password)') }}</option>
                                <option value="pjsip_ip">{{ __('PJSIP IP Authentication (Static IP)') }}</option>
                                <option value="sip">{{ __('Standard SIP') }}</option>
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('SIP Host / Server / FQDN') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_trunk_host" name="host" class="form-control form-control-sm font-monospace" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Port') }}</label>
                            <input type="number" id="edit_trunk_port" name="port" class="form-control form-control-sm font-monospace" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Transport') }}</label>
                            <select id="edit_trunk_transport" name="transport" class="form-select form-select-sm">
                                <option value="udp">UDP</option>
                                <option value="tcp">TCP</option>
                                <option value="tls">TLS (Secure)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('SIP Auth Username / Account') }}</label>
                            <input type="text" id="edit_trunk_username" name="username" class="form-control form-control-sm font-monospace">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('SIP Auth Password (leave blank to keep)') }}</label>
                            <input type="password" name="secret" class="form-control form-control-sm font-monospace" placeholder="••••••••">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Outbound Caller ID (DID)') }}</label>
                            <input type="text" id="edit_trunk_outbound_caller_id" name="outbound_caller_id" class="form-control form-control-sm font-monospace">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Caller Name Display') }}</label>
                            <input type="text" id="edit_trunk_caller_name" name="caller_name" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Max Channels') }}</label>
                            <input type="number" id="edit_trunk_max_channels" name="max_channels" class="form-control form-control-sm" min="1" max="500">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Allowed Codecs') }}</label>
                            <input type="text" id="edit_trunk_codecs" name="codecs" class="form-control form-control-sm font-monospace">
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Notes') }}</label>
                            <textarea id="edit_trunk_notes" name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_registration" value="1" id="edit_trunk_reg">
                                <label class="form-check-label text-dark small" for="edit_trunk_reg">
                                    {{ __('Send outbound SIP registration to carrier') }}
                                </label>
                            </div>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_trunk_active">
                                <label class="form-check-label text-dark small" for="edit_trunk_active">
                                    {{ __('Activate trunk') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Update Trunk') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function openEditTrunkModal(trunk) {
        document.getElementById('editTrunkForm').action = `/admin/pbx/trunks/${trunk.id}`;
        document.getElementById('edit_trunk_name').value = trunk.name;
        document.getElementById('edit_trunk_type').value = trunk.type;
        document.getElementById('edit_trunk_host').value = trunk.host;
        document.getElementById('edit_trunk_port').value = trunk.port || 5060;
        document.getElementById('edit_trunk_transport').value = trunk.transport || 'udp';
        document.getElementById('edit_trunk_username').value = trunk.username || '';
        document.getElementById('edit_trunk_outbound_caller_id').value = trunk.outbound_caller_id || '';
        document.getElementById('edit_trunk_caller_name').value = trunk.caller_name || '';
        document.getElementById('edit_trunk_max_channels').value = trunk.max_channels || 10;
        document.getElementById('edit_trunk_codecs').value = trunk.codecs || 'ulaw,alaw,g729,opus';
        document.getElementById('edit_trunk_notes').value = trunk.notes || '';
        document.getElementById('edit_trunk_reg').checked = !!trunk.is_registration;
        document.getElementById('edit_trunk_active').checked = !!trunk.is_active;

        const modalEl = document.getElementById('editTrunkModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endpush
