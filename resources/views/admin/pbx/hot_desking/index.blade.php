@extends('admin.layouts.app')
@section('title', __('Hot Desking & Extension Mobility'))
@section('page-title', __('Hot Desking Stations'))

@section('content')
<div class="row g-3">
    <!-- Top Action Deck -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="shuffle" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Hot Desking & Extension Mobility') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Dynamic agent seat switching, hardware IP phone roaming, and dialplan PIN authentication.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();">
                        <i data-lucide="phone-call" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Launch Softphone') }}</span>
                    </button>
                    <a href="{{ route('admin.pbx.extensions.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5">
                        <i data-lucide="phone" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Extensions') }}</span>
                    </a>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createStationModal">
                        <i data-lucide="plus" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Add Hot Desk') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Headline KPI Cards -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Total Desks') }}</span>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="monitor" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalStations }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="layers" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Physical Hardware Endpoints') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Occupied Desks') }}</span>
                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $occupiedStations }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-success">
                    <span class="badge-dot-live"></span>
                    <span>{{ __('Active Agent Shifts') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Available Seats') }}</span>
                    <div class="rounded-3 bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $availableStations }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-info">
                    <i data-lucide="wifi" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Ready for Next Shift') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Dialplan Codes') }}</span>
                    <div class="rounded-3 bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="hash" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h5 fw-bold mb-1 font-monospace text-dark">*70 / *71 / *72</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="key" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Login • Status • Logout') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Codes Banner -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.04) 0%, rgba(16, 185, 129, 0.04) 100%);">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-primary fs-6 font-monospace px-2.5 py-1.5">*70</span>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Hot Desk Login') }}</div>
                                <small class="text-muted">{{ __('Dial from any IP phone, enter Ext + PIN to claim desk.') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-info fs-6 font-monospace px-2.5 py-1.5">*71</span>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Desk Status Check') }}</div>
                                <small class="text-muted">{{ __('Hear voice announcement of currently active extension.') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-danger fs-6 font-monospace px-2.5 py-1.5">*72</span>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Hot Desk Logout') }}</div>
                                <small class="text-muted">{{ __('Release station and revert to available generic pool.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hot Desk Stations Table Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="monitor" class="text-primary" style="width: 18px; height: 18px;"></i>
                        <span>{{ __('Hot Desk Hardware Stations') }}</span>
                    </h6>
                    <small class="text-muted">{{ __('Physical desk phones mapped to dynamic employee extensions') }}</small>
                </div>
                <span class="badge bg-light text-secondary border font-monospace">
                    {{ $stations->count() }} {{ __('Registered Desks') }}
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="ps-3 ps-md-4">{{ __('ID') }}</th>
                                <th>{{ __('Desk / Station') }}</th>
                                <th>{{ __('Hardware Device') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Assigned Extension') }}</th>
                                <th style="width: 120px;">{{ __('Status') }}</th>
                                <th style="width: 140px;">{{ __('Shift Time') }}</th>
                                <th class="text-end pe-3 pe-md-4" style="width: 180px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stations as $station)
                                <tr>
                                    <td class="ps-3 ps-md-4 font-monospace text-muted small">
                                        #{{ $station->id }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $station->name }}</div>
                                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 10px;">
                                            {{ $station->station_endpoint }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $station->device_model }}</div>
                                        <div class="text-muted font-monospace" style="font-size: 11px;">
                                            {{ $station->ip_address ?: 'DHCP Dynamic' }}
                                            @if($station->mac_address)
                                                • {{ $station->mac_address }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-dark">{{ $station->location ?: __('Unspecified') }}</div>
                                        @if($station->notes)
                                            <small class="text-muted text-truncate d-block" style="max-width: 180px;" title="{{ $station->notes }}">{{ $station->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($station->current_extension)
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold font-monospace px-2 py-1">
                                                    Ext {{ $station->current_extension }}
                                                </span>
                                                <div>
                                                    <div class="fw-semibold text-dark small" style="line-height: 1.2;">{{ $station->current_user_name }}</div>
                                                    <small class="text-muted" style="font-size: 11px;">{{ __('PIN') }}: ****</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">
                                                <i data-lucide="user-x" class="me-1" style="width: 12px; height: 12px;"></i>
                                                {{ __('No Agent Logged In') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($station->status === 'occupied')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2.5 py-1">
                                                <span class="badge-dot-live"></span>
                                                <span>{{ __('In Use') }}</span>
                                            </span>
                                        @elseif($station->status === 'available')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1">
                                                <i data-lucide="check" class="me-1" style="width: 12px; height: 12px;"></i>
                                                {{ __('Available') }}
                                            </span>
                                        @elseif($station->status === 'maintenance')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1">
                                                {{ __('Maintenance') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">
                                                {{ ucfirst($station->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($station->logged_in_at)
                                            <span title="{{ $station->logged_in_at->format('Y-m-d H:i:s') }}">
                                                {{ $station->logged_in_at->diffForHumans(null, true) }} ago
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3 pe-md-4">
                                        <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                            @if($station->status === 'occupied' && $station->current_extension)
                                                <!-- Call Assigned Extension Icon Button -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success p-0 d-inline-flex align-items-center justify-content-center" 
                                                        style="width: 32px; height: 32px; border-radius: 8px;" 
                                                        onclick="if (typeof togglePbxDialer === 'function') { togglePbxDialer(); pbxCallContact('{{ $station->current_extension }}', 'Desk {{ $station->name }}'); }" 
                                                        title="{{ __('Call Desk Ext') }} {{ $station->current_extension }}" 
                                                        data-bs-toggle="tooltip">
                                                    <i data-lucide="phone-call" style="width: 15px; height: 15px;"></i>
                                                </button>

                                                <!-- Force Release / Logout Icon Button -->
                                                <form action="{{ route('admin.pbx.hot-desking.release', $station->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('{{ __('Release this station and log out the current employee?') }}')">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-warning p-0 d-inline-flex align-items-center justify-content-center" 
                                                            style="width: 32px; height: 32px; border-radius: 8px;" 
                                                            title="{{ __('Log Out Extension (Release Station)') }}" 
                                                            data-bs-toggle="tooltip">
                                                        <i data-lucide="log-out" style="width: 15px; height: 15px;"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <!-- Assign / Login Employee Icon Button -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center" 
                                                        style="width: 32px; height: 32px; border-radius: 8px;" 
                                                        onclick="openAssignModal({{ $station->id }}, '{{ addslashes($station->name) }}', '{{ $station->station_endpoint }}')" 
                                                        title="{{ __('Assign / Log In Extension') }}" 
                                                        data-bs-toggle="tooltip">
                                                    <i data-lucide="log-in" style="width: 15px; height: 15px;"></i>
                                                </button>
                                            @endif

                                            <!-- Edit Station Icon Button -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary p-0 d-inline-flex align-items-center justify-content-center" 
                                                    style="width: 32px; height: 32px; border-radius: 8px;" 
                                                    onclick="openEditStationModal({{ json_encode($station) }})" 
                                                    title="{{ __('Edit Station') }}" 
                                                    data-bs-toggle="tooltip">
                                                <i data-lucide="pencil" style="width: 15px; height: 15px;"></i>
                                            </button>

                                            <!-- Delete Station Icon Button -->
                                            <form action="{{ route('admin.pbx.hot-desking.destroy', $station->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('{{ __('Are you sure you want to delete this hot desk station?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center" 
                                                        style="width: 32px; height: 32px; border-radius: 8px;" 
                                                        title="{{ __('Delete Station') }}" 
                                                        data-bs-toggle="tooltip">
                                                    <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i data-lucide="monitor-off" class="mb-2 text-secondary" style="width: 36px; height: 36px;"></i>
                                        <h6 class="fw-bold text-dark">{{ __('No Hot Desk Stations Provisioned') }}</h6>
                                        <p class="small mb-3">{{ __('Add your physical IP desk phones to allow agents to switch seats dynamically.') }}</p>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStationModal">
                                            <i data-lucide="plus" class="me-1"></i>{{ __('Add First Hot Desk') }}
                                        </button>
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

<!-- Modal: Create Hot Desk Station -->
<div class="modal fade" id="createStationModal" tabindex="-1" aria-labelledby="createStationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.pbx.hot-desking.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="createStationModalLabel">
                        <i data-lucide="plus-circle" class="me-1" style="width: 18px; height: 18px;"></i>
                        {{ __('Add Physical Hot Desk Station') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Desk / Station Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Sales Floor - Desk 01" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Station Hardware Endpoint / SIP ID') }} <span class="text-danger">*</span></label>
                            <input type="text" name="station_endpoint" class="form-control font-monospace" placeholder="e.g. desk-sales-01" required>
                            <small class="text-muted">{{ __('Unique PJSIP identifier or hardware registration name') }}</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Device Model') }} <span class="text-danger">*</span></label>
                            <input type="text" name="device_model" class="form-control" placeholder="e.g. Yealink SIP-T46U" value="Yealink SIP-T46U" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Physical Location / Zone') }}</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Floor 2 - Zone B">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('Device IP Address') }}</label>
                            <input type="text" name="ip_address" class="form-control font-monospace" placeholder="e.g. 192.168.1.101">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('MAC Address') }}</label>
                            <input type="text" name="mac_address" class="form-control font-monospace" placeholder="e.g. 00:15:65:8a:bc:12">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('Station Security PIN') }}</label>
                            <input type="text" name="pin" class="form-control font-monospace" placeholder="1234" value="1234">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">{{ __('Administrative Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Optional notes regarding hardware ports, headsets, or shift assignments...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Save Hot Desk') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Hot Desk Station -->
<div class="modal fade" id="editStationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form id="editStationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-secondary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i data-lucide="edit-3" class="me-1" style="width: 18px; height: 18px;"></i>
                        {{ __('Edit Hot Desk Station') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Desk / Station Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">{{ __('Station Hardware Endpoint') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_station_endpoint" name="station_endpoint" class="form-control font-monospace" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('Device Model') }} <span class="text-danger">*</span></label>
                            <input type="text" id="edit_device_model" name="device_model" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('Status') }}</label>
                            <select id="edit_status" name="status" class="form-select">
                                <option value="available">{{ __('Available') }}</option>
                                <option value="occupied">{{ __('Occupied') }}</option>
                                <option value="maintenance">{{ __('Maintenance') }}</option>
                                <option value="offline">{{ __('Offline') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('Station Security PIN') }}</label>
                            <input type="text" id="edit_pin" name="pin" class="form-control font-monospace">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('Device IP Address') }}</label>
                            <input type="text" id="edit_ip_address" name="ip_address" class="form-control font-monospace">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('MAC Address') }}</label>
                            <input type="text" id="edit_mac_address" name="mac_address" class="form-control font-monospace">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">{{ __('Location') }}</label>
                            <input type="text" id="edit_location" name="location" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">{{ __('Administrative Notes') }}</label>
                            <textarea id="edit_notes" name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Update Station') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Assign Extension (Login) -->
<div class="modal fade" id="assignStationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="assignStationForm" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i data-lucide="log-in" class="me-1" style="width: 18px; height: 18px;"></i>
                        {{ __('Assign Employee to Station') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <span id="assignStationAlertDesk" class="fw-bold"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">{{ __('Select Employee Extension') }} <span class="text-danger">*</span></label>
                        <select name="extension_id" class="form-select" required>
                            <option value="">{{ __('-- Choose Active Employee Extension --') }}</option>
                            @foreach($extensions as $e)
                                <option value="{{ $e->id }}">
                                    Ext {{ $e->extension }} - {{ $e->user?->name ?: $e->name }} ({{ $e->user?->email ?: 'Internal' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">{{ __('The physical IP desk phone will instantly adopt this extension identity and ring on incoming calls.') }}</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success btn-sm px-3">
                        <i data-lucide="check" class="me-1" style="width: 14px; height: 14px;"></i>
                        {{ __('Log In Extension') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAssignModal(stationId, stationName, endpoint) {
        document.getElementById('assignStationAlertDesk').textContent = `${stationName} (${endpoint})`;
        document.getElementById('assignStationForm').action = `{{ url('admin/pbx/hot-desking') }}/${stationId}/assign`;
        const modal = new bootstrap.Modal(document.getElementById('assignStationModal'));
        modal.show();
    }

    function openEditStationModal(station) {
        document.getElementById('editStationForm').action = `{{ url('admin/pbx/hot-desking') }}/${station.id}`;
        document.getElementById('edit_name').value = station.name || '';
        document.getElementById('edit_station_endpoint').value = station.station_endpoint || '';
        document.getElementById('edit_device_model').value = station.device_model || '';
        document.getElementById('edit_status').value = station.status || 'available';
        document.getElementById('edit_pin').value = station.pin || '1234';
        document.getElementById('edit_ip_address').value = station.ip_address || '';
        document.getElementById('edit_mac_address').value = station.mac_address || '';
        document.getElementById('edit_location').value = station.location || '';
        document.getElementById('edit_notes').value = station.notes || '';

        const modal = new bootstrap.Modal(document.getElementById('editStationModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
