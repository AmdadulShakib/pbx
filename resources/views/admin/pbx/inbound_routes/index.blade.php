@extends('admin.layouts.app')
@section('title', __('Inbound DID Routes'))
@section('page-title', __('Inbound Routes'))

@section('content')
<div class="row g-3">
    <!-- Top Action Header -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="phone-incoming" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Inbound DID Call Routes') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Route incoming telephone numbers (DIDs) from trunks to IVR digital receptionists, ring groups, or extensions.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.pbx.inbound-routes.sync') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" title="{{ __('Compile & sync inbound rules to Asterisk') }}">
                            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                            <span>{{ __('Sync to Asterisk') }}</span>
                        </button>
                    </form>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createInboundRouteModal">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Add Inbound Route') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('dialplan_snippet'))
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-dark mb-0">{{ __('Compiled [from-trunk] Dialplan') }}</h6>
                        <span class="badge bg-success-subtle text-success">{{ __('Asterisk Reloaded') }}</span>
                    </div>
                    <pre class="bg-dark text-light p-3 rounded-3 small font-monospace mb-0" style="max-height: 180px; overflow-y: auto;">{{ session('dialplan_snippet') }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- Inbound Routes Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">{{ __('Configured Inbound Routing Rules') }}</h6>
                <span class="badge bg-primary rounded-pill px-2.5 py-1">{{ count($routes) }} {{ __('Total') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-custom-head text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 ps-md-4 py-3">{{ __('Priority') }}</th>
                            <th class="py-3">{{ __('Route Description') }}</th>
                            <th class="py-3">{{ __('DID Pattern') }}</th>
                            <th class="py-3">{{ __('Caller ID Match') }}</th>
                            <th class="py-3">{{ __('Destination Target') }}</th>
                            <th class="py-3">{{ __('CID Tag') }}</th>
                            <th class="py-3">{{ __('Status') }}</th>
                            <th class="text-end pe-3 pe-md-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $r)
                            <tr>
                                <td class="ps-3 ps-md-4 py-3">
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        #{{ $r->priority }}
                                    </span>
                                </td>

                                <td class="py-3">
                                    <div class="fw-bold text-dark">{{ $r->name }}</div>
                                    @if($r->notes)
                                        <small class="text-muted">{{ Str::limit($r->notes, 35) }}</small>
                                    @endif
                                </td>

                                <td class="py-3 font-monospace">
                                    <span class="badge bg-light text-primary border px-2 py-1">
                                        {{ $r->did_pattern }}
                                    </span>
                                </td>

                                <td class="py-3 font-monospace small text-muted">
                                    {{ $r->caller_id_pattern ?: 'ANY' }}
                                </td>

                                <td class="py-3">
                                    @if($r->destination_type === 'ivr')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                            IVR &rarr; {{ $r->destination_id }}
                                        </span>
                                    @elseif($r->destination_type === 'ring_group')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            Ring Group &rarr; {{ $r->destination_id }}
                                        </span>
                                    @elseif($r->destination_type === 'hangup')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            Hangup Call
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                            Ext {{ $r->destination_id }}
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3 font-monospace small">
                                    @if($r->cid_prefix)
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5">{{ $r->cid_prefix }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="py-3">
                                    @if($r->is_active)
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
                                                onclick="openEditInboundModal({{ json_encode($r) }})" 
                                                title="{{ __('Edit Inbound Route') }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.pbx.inbound-routes.destroy', $r->id) }}" class="d-inline m-0" onsubmit="return confirm('{{ __('Are you sure you want to delete this route?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger btn-action-icon" 
                                                    title="{{ __('Delete Inbound Route') }}" 
                                                    data-bs-toggle="tooltip">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="rounded-circle bg-light border p-3 mb-3 text-muted">
                                            <i data-lucide="phone-incoming" style="width: 28px; height: 28px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('No Inbound Routes Configured') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('Create an inbound route to direct incoming phone calls to your IVR menu or extensions.') }}</p>
                                        <button type="button" class="btn btn-primary btn-sm px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createInboundRouteModal">
                                            {{ __('Add First Route') }}
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

<!-- Create Inbound Route Modal -->
<div class="modal fade" id="createInboundRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.pbx.inbound-routes.store') }}">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Add Inbound DID Call Route') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Route Description / Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Main Toll-Free DID -> Main IVR" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Evaluation Priority') }}</label>
                            <input type="number" name="priority" class="form-control form-control-sm font-monospace" value="1" min="1" max="999" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('DID Number Pattern') }} <span class="text-danger">*</span></label>
                            <input type="text" name="did_pattern" class="form-control form-control-sm font-monospace" placeholder="e.g. +18005550199 or ANY" value="ANY" required>
                            <small class="text-muted" style="font-size: 11px;">Enter specific number or <code>ANY</code> to match all incoming calls.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Caller ID Pattern (Optional)') }}</label>
                            <input type="text" name="caller_id_pattern" class="form-control form-control-sm font-monospace" placeholder="ANY or VIP number" value="ANY">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Destination Type') }} <span class="text-danger">*</span></label>
                            <select name="destination_type" class="form-select form-select-sm" id="create_inbound_type" onchange="updateInboundDestOptions('create_inbound_type', 'create_inbound_dest')" required>
                                <option value="ivr">{{ __('IVR Digital Receptionist') }}</option>
                                <option value="ring_group">{{ __('Ring Group') }}</option>
                                <option value="extension" selected>{{ __('Specific Extension') }}</option>
                                <option value="hangup">{{ __('Hangup / Reject Call') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Destination Target') }} <span class="text-danger">*</span></label>
                            <select name="destination_id" class="form-select form-select-sm" id="create_inbound_dest" required>
                                @foreach($extensions as $ext)
                                    <option value="{{ $ext->extension }}">Ext {{ $ext->extension }} - {{ $ext->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Prepend Caller ID Name Tag') }}</label>
                            <input type="text" name="cid_prefix" class="form-control form-control-sm" placeholder="e.g. [SALES] or [VIP]">
                            <small class="text-muted" style="font-size: 11px;">Agent softphone will display this tag before caller name.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional internal notes">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_inbound_active" checked>
                                <label class="form-check-label text-dark small" for="create_inbound_active">
                                    {{ __('Activate route immediately') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Save Inbound Route') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Inbound Route Modal -->
<div class="modal fade" id="editInboundRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editInboundRouteForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Edit Inbound DID Call Route') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Route Description / Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_inbound_name" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Evaluation Priority') }}</label>
                            <input type="number" id="edit_inbound_priority" name="priority" class="form-control form-control-sm font-monospace" min="1" max="999" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('DID Number Pattern') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_inbound_did" name="did_pattern" class="form-control form-control-sm font-monospace" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Caller ID Pattern') }}</label>
                            <input type="text" id="edit_inbound_cid" name="caller_id_pattern" class="form-control form-control-sm font-monospace">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Destination Type') }} <span class="text-danger">*</span></label>
                            <select id="edit_inbound_type" name="destination_type" class="form-select form-select-sm" onchange="updateInboundDestOptions('edit_inbound_type', 'edit_inbound_dest')" required>
                                <option value="ivr">{{ __('IVR Digital Receptionist') }}</option>
                                <option value="ring_group">{{ __('Ring Group') }}</option>
                                <option value="extension">{{ __('Specific Extension') }}</option>
                                <option value="hangup">{{ __('Hangup / Reject Call') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Destination Target') }} <span class="text-danger">*</span></label>
                            <select id="edit_inbound_dest" name="destination_id" class="form-select form-select-sm" required>
                                @foreach($extensions as $ext)
                                    <option value="{{ $ext->extension }}">Ext {{ $ext->extension }} - {{ $ext->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Prepend Caller ID Name Tag') }}</label>
                            <input type="text" id="edit_inbound_cid_prefix" name="cid_prefix" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Notes') }}</label>
                            <input type="text" id="edit_inbound_notes" name="notes" class="form-control form-control-sm">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_inbound_active">
                                <label class="form-check-label text-dark small" for="edit_inbound_active">
                                    {{ __('Activate route') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Update Inbound Route') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const extensionsData = @json($extensions);
    const ivrsData = @json($ivrs);
    const ringGroupsData = @json($ringGroups);

    function updateInboundDestOptions(typeSelectId, destSelectId, selectedVal = null) {
        const type = document.getElementById(typeSelectId).value;
        const destSelect = document.getElementById(destSelectId);
        destSelect.innerHTML = '';

        if (type === 'ivr') {
            if (ivrsData.length === 0) {
                destSelect.innerHTML = '<option value="">No IVRs configured</option>';
            } else {
                ivrsData.forEach(ivr => {
                    const sel = (ivr.extension == selectedVal) ? 'selected' : '';
                    destSelect.innerHTML += `<option value="${ivr.extension}" ${sel}>IVR Ext ${ivr.extension} - ${ivr.name}</option>`;
                });
            }
        } else if (type === 'ring_group') {
            if (ringGroupsData.length === 0) {
                destSelect.innerHTML = '<option value="">No Ring Groups configured</option>';
            } else {
                ringGroupsData.forEach(rg => {
                    const sel = (rg.extension == selectedVal) ? 'selected' : '';
                    destSelect.innerHTML += `<option value="${rg.extension}" ${sel}>Group Ext ${rg.extension} - ${rg.name}</option>`;
                });
            }
        } else if (type === 'hangup') {
            destSelect.innerHTML = '<option value="hangup" selected>Hangup Immediately</option>';
        } else {
            extensionsData.forEach(ext => {
                const sel = (ext.extension == selectedVal) ? 'selected' : '';
                destSelect.innerHTML += `<option value="${ext.extension}" ${sel}>Ext ${ext.extension} - ${ext.name}</option>`;
            });
        }
    }

    function openEditInboundModal(r) {
        document.getElementById('editInboundRouteForm').action = `/admin/pbx/inbound-routes/${r.id}`;
        document.getElementById('edit_inbound_name').value = r.name;
        document.getElementById('edit_inbound_priority').value = r.priority || 1;
        document.getElementById('edit_inbound_did').value = r.did_pattern;
        document.getElementById('edit_inbound_cid').value = r.caller_id_pattern || 'ANY';
        document.getElementById('edit_inbound_type').value = r.destination_type || 'extension';
        updateInboundDestOptions('edit_inbound_type', 'edit_inbound_dest', r.destination_id);
        document.getElementById('edit_inbound_cid_prefix').value = r.cid_prefix || '';
        document.getElementById('edit_inbound_notes').value = r.notes || '';
        document.getElementById('edit_inbound_active').checked = !!r.is_active;

        const modalEl = document.getElementById('editInboundRouteModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endpush
