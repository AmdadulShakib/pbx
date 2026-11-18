@extends('admin.layouts.app')
@section('title', __('Outbound Dial Routes'))
@section('page-title', __('Outbound Routes'))

@section('content')
<div class="row g-3">
    <!-- Top Action Header -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="phone-outgoing" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Outbound Carrier Dial Routes') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Define dial pattern matching, prefix manipulation, and route external calls through primary & failover SIP trunks.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.pbx.outbound-routes.sync') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" title="{{ __('Compile & sync outbound rules to Asterisk') }}">
                            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                            <span>{{ __('Sync to Asterisk') }}</span>
                        </button>
                    </form>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createOutboundRouteModal">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Add Outbound Route') }}</span>
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
                        <h6 class="fw-bold text-dark mb-0">{{ __('Compiled [from-internal] Outbound Dialplan') }}</h6>
                        <span class="badge bg-success-subtle text-success">{{ __('Asterisk Reloaded') }}</span>
                    </div>
                    <pre class="bg-dark text-light p-3 rounded-3 small font-monospace mb-0" style="max-height: 180px; overflow-y: auto;">{{ session('dialplan_snippet') }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- Outbound Routes Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">{{ __('Configured Outbound Dial Rules') }}</h6>
                <span class="badge bg-primary rounded-pill px-2.5 py-1">{{ count($routes) }} {{ __('Total') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-custom-head text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 ps-md-4 py-3">{{ __('Order') }}</th>
                            <th class="py-3">{{ __('Route Name') }}</th>
                            <th class="py-3">{{ __('Dial Pattern') }}</th>
                            <th class="py-3">{{ __('Prefix Rules') }}</th>
                            <th class="py-3">{{ __('Primary Trunk') }}</th>
                            <th class="py-3">{{ __('Failover Trunk') }}</th>
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
                                    <span class="badge bg-light text-primary border px-2 py-1 font-monospace">
                                        {{ $r->dial_pattern }}
                                    </span>
                                </td>

                                <td class="py-3 font-monospace small">
                                    <div><span class="text-muted">Strip:</span> {{ $r->strip_digits }}</div>
                                    @if($r->prepend_digits)
                                        <div><span class="text-muted">Prepend:</span> {{ $r->prepend_digits }}</div>
                                    @endif
                                </td>

                                <td class="py-3">
                                    @if($r->primaryTrunk)
                                        <span class="badge bg-light text-dark border px-2 py-1 d-inline-flex align-items-center gap-1">
                                            <i data-lucide="network" style="width: 12px; height: 12px;"></i>
                                            <span>{{ $r->primaryTrunk->name }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted small">{{ __('None selected') }}</span>
                                    @endif
                                </td>

                                <td class="py-3">
                                    @if($r->failoverTrunk)
                                        <span class="badge bg-light text-secondary border px-2 py-1 d-inline-flex align-items-center gap-1">
                                            <i data-lucide="shield" style="width: 12px; height: 12px;"></i>
                                            <span>{{ $r->failoverTrunk->name }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
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
                                                onclick="openEditOutboundModal({{ json_encode($r) }})" 
                                                title="{{ __('Edit Outbound Route') }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.pbx.outbound-routes.destroy', $r->id) }}" class="d-inline m-0" onsubmit="return confirm('{{ __('Are you sure you want to delete this route?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger btn-action-icon" 
                                                    title="{{ __('Delete Outbound Route') }}" 
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
                                            <i data-lucide="phone-outgoing" style="width: 28px; height: 28px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('No Outbound Routes Configured') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('Add outbound dial rules to allow your agents to call customer mobile phones or PSTN numbers.') }}</p>
                                        <button type="button" class="btn btn-primary btn-sm px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createOutboundRouteModal">
                                            {{ __('Add First Outbound Route') }}
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

<!-- Create Outbound Route Modal -->
<div class="modal fade" id="createOutboundRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.pbx.outbound-routes.store') }}">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Add Outbound Dial Route') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Route Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Bangladesh Mobile (01X) or International" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Evaluation Order') }}</label>
                            <input type="number" name="priority" class="form-control form-control-sm font-monospace" value="1" min="1" max="999" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Dial Pattern') }} <span class="text-danger">*</span></label>
                            <input type="text" name="dial_pattern" class="form-control form-control-sm font-monospace" placeholder="e.g. _01XXXXXXXXX or _X." value="_X." required>
                            <small class="text-muted" style="font-size: 11px;">Asterisk pattern: <code>_01XXXXXXXXX</code> (11 digits), <code>_+X.</code> (plus), <code>_X.</code> (all)</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Strip Digits') }}</label>
                            <input type="number" name="strip_digits" class="form-control form-control-sm font-monospace" value="0" min="0" max="20" required>
                            <small class="text-muted" style="font-size: 11px;">Digits to remove from front</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Prepend Digits') }}</label>
                            <input type="text" name="prepend_digits" class="form-control form-control-sm font-monospace" placeholder="e.g. +880 or 1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Primary Carrier Trunk') }} <span class="text-danger">*</span></label>
                            <select name="primary_trunk_id" class="form-select form-select-sm" required>
                                <option value="">-- {{ __('Select Trunk') }} --</option>
                                @foreach($trunks as $trunk)
                                    <option value="{{ $trunk->id }}">{{ $trunk->name }} ({{ $trunk->host }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Failover Trunk (Backup)') }}</label>
                            <select name="failover_trunk_id" class="form-select form-select-sm">
                                <option value="">-- {{ __('None / Fail immediately') }} --</option>
                                @foreach($trunks as $trunk)
                                    <option value="{{ $trunk->id }}">{{ $trunk->name }} ({{ $trunk->host }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Caller ID Number Override') }}</label>
                            <input type="text" name="caller_id_override" class="form-control form-control-sm font-monospace" placeholder="e.g. +18005550199">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional notes">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_outbound_active" checked>
                                <label class="form-check-label text-dark small" for="create_outbound_active">
                                    {{ __('Activate route immediately') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Save Outbound Route') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Outbound Route Modal -->
<div class="modal fade" id="editOutboundRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editOutboundRouteForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Edit Outbound Dial Route') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Route Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_outbound_name" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Evaluation Order') }}</label>
                            <input type="number" id="edit_outbound_priority" name="priority" class="form-control form-control-sm font-monospace" min="1" max="999" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Dial Pattern') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_outbound_pattern" name="dial_pattern" class="form-control form-control-sm font-monospace" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Strip Digits') }}</label>
                            <input type="number" id="edit_outbound_strip" name="strip_digits" class="form-control form-control-sm font-monospace" min="0" max="20" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Prepend Digits') }}</label>
                            <input type="text" id="edit_outbound_prepend" name="prepend_digits" class="form-control form-control-sm font-monospace">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Primary Carrier Trunk') }} <span class="text-danger">*</span></label>
                            <select id="edit_outbound_primary_trunk" name="primary_trunk_id" class="form-select form-select-sm" required>
                                <option value="">-- {{ __('Select Trunk') }} --</option>
                                @foreach($trunks as $trunk)
                                    <option value="{{ $trunk->id }}">{{ $trunk->name }} ({{ $trunk->host }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Failover Trunk (Backup)') }}</label>
                            <select id="edit_outbound_failover_trunk" name="failover_trunk_id" class="form-select form-select-sm">
                                <option value="">-- {{ __('None / Fail immediately') }} --</option>
                                @foreach($trunks as $trunk)
                                    <option value="{{ $trunk->id }}">{{ $trunk->name }} ({{ $trunk->host }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Caller ID Number Override') }}</label>
                            <input type="text" id="edit_outbound_caller_id" name="caller_id_override" class="form-control form-control-sm font-monospace">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Notes') }}</label>
                            <input type="text" id="edit_outbound_notes" name="notes" class="form-control form-control-sm">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_outbound_active">
                                <label class="form-check-label text-dark small" for="edit_outbound_active">
                                    {{ __('Activate route') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Update Outbound Route') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function openEditOutboundModal(r) {
        document.getElementById('editOutboundRouteForm').action = `/admin/pbx/outbound-routes/${r.id}`;
        document.getElementById('edit_outbound_name').value = r.name;
        document.getElementById('edit_outbound_priority').value = r.priority || 1;
        document.getElementById('edit_outbound_pattern').value = r.dial_pattern;
        document.getElementById('edit_outbound_strip').value = r.strip_digits || 0;
        document.getElementById('edit_outbound_prepend').value = r.prepend_digits || '';
        document.getElementById('edit_outbound_primary_trunk').value = r.primary_trunk_id || '';
        document.getElementById('edit_outbound_failover_trunk').value = r.failover_trunk_id || '';
        document.getElementById('edit_outbound_caller_id').value = r.caller_id_override || '';
        document.getElementById('edit_outbound_notes').value = r.notes || '';
        document.getElementById('edit_outbound_active').checked = !!r.is_active;

        const modalEl = document.getElementById('editOutboundRouteModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endpush
