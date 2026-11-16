@extends('admin.layouts.app')
@section('title', __('IVR Digital Receptionist & Auto-Attendant'))
@section('page-title', __('IVR Menus'))

@section('content')
<div class="row g-3">
    <!-- Top Action Deck -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="layers" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Interactive Voice Response (IVR)') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Digital auto-receptionist with custom greetings, DTMF keypress routing, and fallback handling.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.pbx.ivr.sync') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" title="{{ __('Compile & sync dialplan rules to Asterisk') }}">
                            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                            <span>{{ __('Sync Dialplan') }}</span>
                        </button>
                    </form>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createIvrModal">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Create IVR Menu') }}</span>
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
                        <h6 class="fw-bold text-dark mb-0">{{ __('Generated Asterisk Dialplan Snippet') }}</h6>
                        <span class="badge bg-success-subtle text-success">{{ __('Asterisk Reloaded') }}</span>
                    </div>
                    <pre class="bg-dark text-light p-3 rounded-3 small font-monospace mb-0" style="max-height: 180px; overflow-y: auto;">{{ session('dialplan_snippet') }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- IVR List -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">{{ __('Configured IVR Menus') }}</h6>
                <span class="badge bg-primary rounded-pill px-2.5 py-1">{{ count($ivrs) }} {{ __('Total') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 ps-md-4 py-3">{{ __('IVR Name') }}</th>
                            <th class="py-3">{{ __('Dial Extension') }}</th>
                            <th class="py-3">{{ __('DTMF Key Routing') }}</th>
                            <th class="py-3">{{ __('Timeout Fallback') }}</th>
                            <th class="py-3">{{ __('Status') }}</th>
                            <th class="text-end pe-3 pe-md-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ivrs as $ivr)
                            <tr>
                                <td class="ps-3 ps-md-4 py-3">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $ivr->name }}</div>
                                        @if($ivr->description)
                                            <small class="text-muted d-block">{{ Str::limit($ivr->description, 45) }}</small>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3">
                                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 fs-6">
                                        Ext {{ $ivr->extension }}
                                    </span>
                                </td>

                                <td class="py-3">
                                    <div class="d-flex flex-wrap gap-1.5" style="max-width: 320px;">
                                        @forelse($ivr->options as $opt)
                                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size: 11px;">
                                                <strong class="font-monospace text-primary">[{{ $opt->digit }}]</strong>
                                                <span>&rarr; Ext {{ $opt->destination }}</span>
                                                @if($opt->label)
                                                    <span class="text-muted">({{ $opt->label }})</span>
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-muted small">{{ __('No DTMF keys assigned') }}</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="py-3 font-monospace small">
                                    <div><span class="text-muted">{{ __('Timeout') }}:</span> Ext {{ $ivr->timeout_destination }} ({{ $ivr->timeout }}s)</div>
                                    <div><span class="text-muted">{{ __('Invalid') }}:</span> Ext {{ $ivr->invalid_destination }}</div>
                                </td>

                                <td class="py-3">
                                    @if($ivr->is_active)
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
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-2.5 py-1" onclick="openEditIvrModal({{ json_encode($ivr) }})" title="{{ __('Edit IVR') }}">
                                            <i data-lucide="edit-3" style="width: 13px; height: 13px;"></i>
                                            <span>{{ __('Edit') }}</span>
                                        </button>
                                        <form method="POST" action="{{ route('admin.pbx.ivr.destroy', $ivr->id) }}" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this IVR menu?') }}')">
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
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="rounded-circle bg-light border p-3 mb-3 text-muted">
                                            <i data-lucide="layers" style="width: 28px; height: 28px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('No IVR Menus Created Yet') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('Create your first digital receptionist to route callers to departments with DTMF keypad presses.') }}</p>
                                        <button type="button" class="btn btn-primary btn-sm px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createIvrModal">
                                            {{ __('Create First IVR') }}
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

<!-- Create IVR Modal -->
<div class="modal fade" id="createIvrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.pbx.ivr.store') }}">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Create Digital Receptionist (IVR)') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('IVR Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Main Company Reception" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Dial Extension') }} <span class="text-danger">*</span></label>
                            <input type="text" name="extension" class="form-control form-control-sm font-monospace" placeholder="e.g. 500 or 800" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Description / Welcome Text') }}</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="e.g. Thank you for calling VoxPulse. Press 1 for Sales, 2 for Support, or stay on the line."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Timeout (Seconds)') }}</label>
                            <input type="number" name="timeout" class="form-control form-control-sm" value="10" min="3" max="60" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Timeout Destination') }}</label>
                            <select name="timeout_destination" class="form-select form-select-sm" required>
                                @foreach($extensions as $ext)
                                    <option value="{{ $ext->extension }}">Ext {{ $ext->extension }} - {{ $ext->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Invalid Key Destination') }}</label>
                            <select name="invalid_destination" class="form-select form-select-sm" required>
                                @foreach($extensions as $ext)
                                    <option value="{{ $ext->extension }}">Ext {{ $ext->extension }} - {{ $ext->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- DTMF Option Rows -->
                        <div class="col-12 mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark small text-uppercase">{{ __('DTMF Key Routing Options') }}</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm py-0.5 px-2" onclick="addOptionRow('create-options-container')">
                                    + {{ __('Add Key') }}
                                </button>
                            </div>
                            <div id="create-options-container" class="d-flex flex-column gap-2">
                                <!-- Default Option Row 1 -->
                                <div class="row g-2 align-items-center option-row">
                                    <div class="col-2">
                                        <input type="text" name="digits[]" class="form-control form-control-sm font-monospace text-center fw-bold" placeholder="1" value="1" required>
                                    </div>
                                    <div class="col-5">
                                        <select name="destinations[]" class="form-select form-select-sm" required>
                                            @foreach($extensions as $ext)
                                                <option value="{{ $ext->extension }}">Ext {{ $ext->extension }} - {{ $ext->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" name="labels[]" class="form-control form-control-sm" placeholder="e.g. Sales Team" value="Sales">
                                    </div>
                                    <div class="col-1 text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.option-row').remove()">&times;</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_is_active" checked>
                                <label class="form-check-label text-dark small" for="create_is_active">
                                    {{ __('Activate this IVR menu immediately') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Save IVR Menu') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit IVR Modal -->
<div class="modal fade" id="editIvrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editIvrForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark">{{ __('Edit Digital Receptionist (IVR)') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('IVR Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Dial Extension') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_extension" name="extension" class="form-control form-control-sm font-monospace" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Description / Welcome Text') }}</label>
                            <textarea id="edit_description" name="description" class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Timeout (Seconds)') }}</label>
                            <input type="number" id="edit_timeout" name="timeout" class="form-control form-control-sm" min="3" max="60" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Timeout Destination') }}</label>
                            <select id="edit_timeout_destination" name="timeout_destination" class="form-select form-select-sm" required>
                                @foreach($extensions as $ext)
                                    <option value="{{ $ext->extension }}">Ext {{ $ext->extension }} - {{ $ext->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">{{ __('Invalid Key Destination') }}</label>
                            <select id="edit_invalid_destination" name="invalid_destination" class="form-select form-select-sm" required>
                                @foreach($extensions as $ext)
                                    <option value="{{ $ext->extension }}">Ext {{ $ext->extension }} - {{ $ext->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- DTMF Option Rows -->
                        <div class="col-12 mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark small text-uppercase">{{ __('DTMF Key Routing Options') }}</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm py-0.5 px-2" onclick="addOptionRow('edit-options-container')">
                                    + {{ __('Add Key') }}
                                </button>
                            </div>
                            <div id="edit-options-container" class="d-flex flex-column gap-2"></div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                                <label class="form-check-label text-dark small" for="edit_is_active">
                                    {{ __('Activate this IVR menu immediately') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Update IVR Menu') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const availableExtensions = @json($extensions);

    function addOptionRow(containerId, digit = '', dest = '', label = '') {
        const container = document.getElementById(containerId);
        if (!container) return;
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-center option-row';

        let optionsHtml = '';
        if (availableExtensions && availableExtensions.length > 0) {
            availableExtensions.forEach(ext => {
                const selected = (String(ext.extension) === String(dest)) ? 'selected' : '';
                optionsHtml += `<option value="${ext.extension}" ${selected}>Ext ${ext.extension} - ${ext.name}</option>`;
            });
        } else {
            optionsHtml = '<option value="101">Ext 101 - Admin</option>';
        }

        row.innerHTML = `
            <div class="col-2">
                <input type="text" name="digits[]" class="form-control form-control-sm font-monospace text-center fw-bold" placeholder="Key" value="${digit}" required>
            </div>
            <div class="col-5">
                <select name="destinations[]" class="form-select form-select-sm" required>
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-4">
                <input type="text" name="labels[]" class="form-control form-control-sm" placeholder="e.g. Sales" value="${label}">
            </div>
            <div class="col-1 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.option-row').remove()" title="Remove">&times;</button>
            </div>
        `;

        container.appendChild(row);
    }

    function openEditIvrModal(ivr) {
        document.getElementById('editIvrForm').action = `/admin/pbx/ivr/${ivr.id}`;
        document.getElementById('edit_name').value = ivr.name;
        document.getElementById('edit_extension').value = ivr.extension;
        document.getElementById('edit_description').value = ivr.description || '';
        document.getElementById('edit_timeout').value = ivr.timeout || 10;
        document.getElementById('edit_timeout_destination').value = ivr.timeout_destination || '101';
        document.getElementById('edit_invalid_destination').value = ivr.invalid_destination || '101';
        document.getElementById('edit_is_active').checked = !!ivr.is_active;

        const container = document.getElementById('edit-options-container');
        container.innerHTML = '';
        if (ivr.options && ivr.options.length > 0) {
            ivr.options.forEach(opt => {
                addOptionRow('edit-options-container', opt.digit, opt.destination, opt.label || '');
            });
        } else {
            addOptionRow('edit-options-container');
        }

        const modalEl = document.getElementById('editIvrModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endpush
