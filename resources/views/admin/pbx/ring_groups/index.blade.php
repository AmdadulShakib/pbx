@extends('admin.layouts.app')
@section('title', __('PBX Ring Groups & Call Hunt'))
@section('page-title', __('Ring Groups'))

@section('content')
<div class="row g-3">
    <!-- Top Action Header -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="users-2" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Ring Groups (Hunt Groups)') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Ring multiple agent extensions simultaneously or sequentially when a department number is called.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.pbx.ring-groups.sync') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" title="{{ __('Compile & sync ring group rules to Asterisk') }}">
                            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                            <span>{{ __('Sync to Asterisk') }}</span>
                        </button>
                    </form>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createRingGroupModal">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Create Ring Group') }}</span>
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
                        <h6 class="fw-bold text-dark mb-0">{{ __('Compiled Ring Group Dialplan') }}</h6>
                        <span class="badge bg-success-subtle text-success">{{ __('Asterisk Reloaded') }}</span>
                    </div>
                    <pre class="bg-dark text-light p-3 rounded-3 small font-monospace mb-0" style="max-height: 180px; overflow-y: auto;">{{ session('dialplan_snippet') }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- Ring Groups Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">{{ __('Configured Ring Groups') }}</h6>
                <span class="badge bg-primary rounded-pill px-2.5 py-1">{{ count($ringGroups) }} {{ __('Total') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 ps-md-4 py-3">{{ __('Group Name') }}</th>
                            <th class="py-3">{{ __('Dial Extension') }}</th>
                            <th class="py-3">{{ __('Strategy') }}</th>
                            <th class="py-3">{{ __('Members') }}</th>
                            <th class="py-3">{{ __('Ring Time & Fallback') }}</th>
                            <th class="py-3">{{ __('Status') }}</th>
                            <th class="text-end pe-3 pe-md-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ringGroups as $rg)
                            <tr>
                                <td class="ps-3 ps-md-4 py-3">
                                    <div class="fw-bold text-dark">{{ $rg->name }}</div>
                                    @if($rg->description)
                                        <small class="text-muted">{{ Str::limit($rg->description, 40) }}</small>
                                    @endif
                                </td>

                                <td class="py-3">
                                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 fs-6">
                                        Ext {{ $rg->extension }}
                                    </span>
                                </td>

                                <td class="py-3">
                                    @if($rg->strategy === 'hunt')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            {{ __('Hunt (Sequential)') }}
                                        </span>
                                    @elseif($rg->strategy === 'random')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                            {{ __('Random') }}
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            {{ __('Ring All (Simultaneous)') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3">
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                        @if(is_array($rg->members))
                                            @foreach($rg->members as $mem)
                                                <span class="badge bg-light text-dark border font-monospace px-1.5 py-0.5" style="font-size: 11px;">
                                                    Ext {{ $mem }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">{{ __('None') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3 small">
                                    <div><span class="text-muted">{{ __('Timer') }}:</span> <strong>{{ $rg->ring_time }}s</strong></div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        {{ __('Failover') }}: {{ ucfirst($rg->failover_type) }} &rarr; {{ $rg->failover_destination }}
                                    </div>
                                </td>

                                <td class="py-3">
                                    @if($rg->is_active)
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
                                    <div class="d-inline-flex align-items-center gap-1.5">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-2.5 py-1" onclick="openEditRingGroupModal({{ json_encode($rg) }})" title="{{ __('Edit') }}">
                                            <i data-lucide="edit-3" style="width: 13px; height: 13px;"></i>
                                            <span>{{ __('Edit') }}</span>
                                        </button>
                                        <form method="POST" action="{{ route('admin.pbx.ring-groups.destroy', $rg->id) }}" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this ring group?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1" title="{{ __('Delete') }}">
                                                <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
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
                                            <i data-lucide="users-2" style="width: 28px; height: 28px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('No Ring Groups Configured') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('Create a ring group to ring multiple agent phones at once for Sales, Support, or Billing.') }}</p>
                                        <button type="button" class="btn btn-primary btn-sm px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createRingGroupModal">
                                            {{ __('Create First Ring Group') }}
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

<!-- Create Ring Group Modal -->
<div class="modal fade" id="createRingGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.pbx.ring-groups.store') }}">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Create PBX Ring Group') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Group Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Sales Department" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Dial Extension') }} <span class="text-danger">*</span></label>
                            <input type="text" name="extension" class="form-control form-control-sm font-monospace" placeholder="e.g. 600 or 601" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Ring Strategy') }} <span class="text-danger">*</span></label>
                            <select name="strategy" class="form-select form-select-sm" required>
                                <option value="ringall">{{ __('Ring All (Simultaneously ring all members)') }}</option>
                                <option value="hunt">{{ __('Hunt (Sequential ringing 1-by-1)') }}</option>
                                <option value="random">{{ __('Random Extension') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Ring Time (Seconds)') }} <span class="text-danger">*</span></label>
                            <input type="number" name="ring_time" class="form-control form-control-sm" value="20" min="5" max="120" required>
                        </div>

                        <!-- Member Extension Checkboxes -->
                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold mb-1">{{ __('Select Extension Members') }} <span class="text-danger">*</span></label>
                            <div class="border rounded-2 p-3 bg-light" style="max-height: 160px; overflow-y: auto;">
                                <div class="row g-2">
                                    @foreach($extensions as $ext)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="members[]" value="{{ $ext->extension }}" id="create_mem_{{ $ext->id }}">
                                                <label class="form-check-label small text-dark" for="create_mem_{{ $ext->id }}">
                                                    <strong>Ext {{ $ext->extension }}</strong> - {{ $ext->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('No-Answer Failover Type') }}</label>
                            <select name="failover_type" class="form-select form-select-sm" id="create_rg_failover_type">
                                <option value="extension">{{ __('Forward to Extension') }}</option>
                                <option value="ivr">{{ __('Forward to IVR Menu') }}</option>
                                <option value="hangup">{{ __('Hangup') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Failover Destination') }}</label>
                            <input type="text" name="failover_destination" class="form-control form-control-sm font-monospace" placeholder="e.g. 101 or 500" value="101">
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Description') }}</label>
                            <input type="text" name="description" class="form-control form-control-sm" placeholder="Optional description for staff">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_rg_active" checked>
                                <label class="form-check-label text-dark small" for="create_rg_active">
                                    {{ __('Activate ring group immediately') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Save Ring Group') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Ring Group Modal -->
<div class="modal fade" id="editRingGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editRingGroupForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Edit PBX Ring Group') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Group Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_rg_name" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Dial Extension') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_rg_extension" name="extension" class="form-control form-control-sm font-monospace" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Ring Strategy') }} <span class="text-danger">*</span></label>
                            <select id="edit_rg_strategy" name="strategy" class="form-select form-select-sm" required>
                                <option value="ringall">{{ __('Ring All (Simultaneously ring all members)') }}</option>
                                <option value="hunt">{{ __('Hunt (Sequential ringing 1-by-1)') }}</option>
                                <option value="random">{{ __('Random Extension') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Ring Time (Seconds)') }} <span class="text-danger">*</span></label>
                            <input type="number" id="edit_rg_ring_time" name="ring_time" class="form-control form-control-sm" min="5" max="120" required>
                        </div>

                        <!-- Member Extension Checkboxes -->
                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold mb-1">{{ __('Select Extension Members') }} <span class="text-danger">*</span></label>
                            <div class="border rounded-2 p-3 bg-light" style="max-height: 160px; overflow-y: auto;">
                                <div class="row g-2">
                                    @foreach($extensions as $ext)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input edit-rg-member" type="checkbox" name="members[]" value="{{ $ext->extension }}" id="edit_mem_{{ $ext->id }}">
                                                <label class="form-check-label small text-dark" for="edit_mem_{{ $ext->id }}">
                                                    <strong>Ext {{ $ext->extension }}</strong> - {{ $ext->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('No-Answer Failover Type') }}</label>
                            <select id="edit_rg_failover_type" name="failover_type" class="form-select form-select-sm">
                                <option value="extension">{{ __('Forward to Extension') }}</option>
                                <option value="ivr">{{ __('Forward to IVR Menu') }}</option>
                                <option value="hangup">{{ __('Hangup') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Failover Destination') }}</label>
                            <input type="text" id="edit_rg_failover_dest" name="failover_destination" class="form-control form-control-sm font-monospace">
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Description') }}</label>
                            <input type="text" id="edit_rg_description" name="description" class="form-control form-control-sm">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_rg_active">
                                <label class="form-check-label text-dark small" for="edit_rg_active">
                                    {{ __('Activate ring group') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Update Ring Group') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function openEditRingGroupModal(rg) {
        document.getElementById('editRingGroupForm').action = `/admin/pbx/ring-groups/${rg.id}`;
        document.getElementById('edit_rg_name').value = rg.name;
        document.getElementById('edit_rg_extension').value = rg.extension;
        document.getElementById('edit_rg_strategy').value = rg.strategy || 'ringall';
        document.getElementById('edit_rg_ring_time').value = rg.ring_time || 20;
        document.getElementById('edit_rg_failover_type').value = rg.failover_type || 'extension';
        document.getElementById('edit_rg_failover_dest').value = rg.failover_destination || '101';
        document.getElementById('edit_rg_description').value = rg.description || '';
        document.getElementById('edit_rg_active').checked = !!rg.is_active;

        const members = Array.isArray(rg.members) ? rg.members : [];
        document.querySelectorAll('.edit-rg-member').forEach(cb => {
            cb.checked = members.includes(cb.value);
        });

        const modalEl = document.getElementById('editRingGroupModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endpush
