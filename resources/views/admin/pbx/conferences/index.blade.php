@extends('admin.layouts.app')
@section('title', __('Team Bridge & Multi-Party Conferences'))
@section('page-title', __('Team Bridge & Conference Rooms'))

@section('content')
<div class="row g-3">
    <!-- Top Action Deck -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="users" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Team Bridge & Conference Rooms') }}</h5>
                        <p class="text-muted small mb-0">{{ __('High-definition multi-party audio bridges powered by Asterisk ConfBridge. Anyone can dial or join together.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();">
                        <i data-lucide="phone-call" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Launch Softphone') }}</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                        <i data-lucide="plus" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Add Conference Room') }}</span>
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
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Total Rooms') }}</span>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="radio" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalRooms }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="layers" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Configured Audio Bridges') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Active Bridges') }}</span>
                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="activity" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark" id="kpiActiveRooms">{{ $activeRooms }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-success fw-semibold">
                    <span class="badge-dot bg-success rounded-circle" style="width: 8px; height: 8px;"></span>
                    <span>{{ __('Rooms in Active Session') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Live Callers') }}</span>
                    <div class="rounded-3 bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="headphones" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark" id="kpiLiveUsers">{{ $totalLiveUsers }}</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="user-check" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Staff & Clients Joined') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold letter-spacing-1">{{ __('Quick Join Code') }}</span>
                    <div class="rounded-3 bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i data-lucide="hash" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-primary">700</div>
                <div class="d-flex align-items-center gap-1.5 small text-muted">
                    <i data-lucide="phone-outgoing" style="width: 13px; height: 13px;"></i>
                    <span>{{ __('Dial 700 to Join All-Hands') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Dial Guide Banner -->
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-light-subtle">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4 d-flex align-items-center gap-2.5">
                        <span class="badge bg-primary px-2.5 py-1.5 rounded-pill fs-7 fw-bold">700</span>
                        <div>
                            <div class="fw-bold small text-dark">{{ __('Company All-Hands Bridge') }}</div>
                            <div class="text-muted fs-8">{{ __('Instant team open mic room for all staff members.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-2.5">
                        <span class="badge bg-secondary px-2.5 py-1.5 rounded-pill fs-7 fw-bold">701</span>
                        <div>
                            <div class="fw-bold small text-dark">{{ __('Executive Boardroom') }}</div>
                            <div class="text-muted fs-8">{{ __('PIN-protected leadership & management conference.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-2.5">
                        <span class="badge bg-success px-2.5 py-1.5 rounded-pill fs-7 fw-bold">702</span>
                        <div>
                            <div class="fw-bold small text-dark">{{ __('Support & Ops War Room') }}</div>
                            <div class="text-muted fs-8">{{ __('Rapid incident resolution and team collaboration.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conference Rooms Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="mic" style="width: 18px; height: 18px;" class="text-primary"></i>
                        <span>{{ __('Team Bridge Audio Conference Rooms') }}</span>
                    </h6>
                    <small class="text-muted">{{ __('Multi-party bridges hosted on Asterisk app_confbridge with high-performance audio mixing.') }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold fs-7">
                        {{ $totalRooms }} {{ __('Rooms') }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light fs-8 text-uppercase fw-bold text-muted border-top">
                            <tr>
                                <th class="ps-4" style="width: 120px;">{{ __('Room #') }}</th>
                                <th>{{ __('Conference Name & Topic') }}</th>
                                <th>{{ __('Access PIN') }}</th>
                                <th>{{ __('Max Capacity') }}</th>
                                <th>{{ __('Live Status') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end pe-4" style="width: 160px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($rooms as $room)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-3 fw-bold font-monospace fs-6">
                                        {{ $room->room_number }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-0.5">{{ $room->name }}</div>
                                    <div class="text-muted fs-8">{{ $room->description ?: __('Multi-party conference audio bridge') }}</div>
                                </td>
                                <td>
                                    @if($room->pin)
                                        <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace">
                                            <i data-lucide="lock" style="width: 12px; height: 12px;" class="text-muted me-1"></i>
                                            PIN: {{ $room->pin }}
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                            <i data-lucide="unlock" style="width: 12px; height: 12px;" class="me-1"></i>
                                            {{ __('Open (No PIN)') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold">{{ $room->max_participants }}</span>
                                    <span class="text-muted fs-8">{{ __('seats') }}</span>
                                </td>
                                <td>
                                    @if($room->live_users > 0)
                                        <span class="badge bg-success text-white px-2.5 py-1.5 rounded-pill d-inline-flex align-items-center gap-1.5 animate-pulse">
                                            <span class="rounded-circle bg-white" style="width: 6px; height: 6px;"></span>
                                            <span>{{ $room->live_users }} {{ __('talking') }}</span>
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill">
                                            {{ __('Idle / Ready') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($room->enabled)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                            ● {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">
                                            ○ {{ __('Disabled') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Join Room via Softphone -->
                                        <button type="button" class="btn btn-sm btn-outline-success p-1.5" onclick="joinConferenceRoom('{{ $room->room_number }}', '{{ addslashes($room->name) }}')" title="{{ __('Join Room via Softphone') }}">
                                            <i data-lucide="phone-call" style="width: 15px; height: 15px;"></i>
                                        </button>
                                        <!-- Edit Room -->
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1.5" data-bs-toggle="modal" data-bs-target="#editRoomModal{{ $room->id }}" title="{{ __('Edit Room') }}">
                                            <i data-lucide="edit-2" style="width: 15px; height: 15px;"></i>
                                        </button>
                                        <!-- Delete Room -->
                                        <form action="{{ route('admin.pbx.conferences.destroy', $room->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete Conference Room {{ $room->room_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1.5" title="{{ __('Delete Room') }}">
                                                <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade text-start" id="editRoomModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.pbx.conferences.update', $room->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                            <i data-lucide="edit-3" style="width: 18px; height: 18px;" class="text-primary"></i>
                                                            <span>{{ __('Edit Conference Room') }} {{ $room->room_number }}</span>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">{{ __('Room Number / Dialplan Extension') }} *</label>
                                                            <input type="text" name="room_number" class="form-control" value="{{ $room->room_number }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">{{ __('Conference Name') }} *</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $room->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">{{ __('Description') }}</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ $room->description }}</textarea>
                                                        </div>
                                                        <div class="row g-2 mb-3">
                                                            <div class="col-6">
                                                                <label class="form-label fw-semibold small text-muted">{{ __('Access PIN (Optional)') }}</label>
                                                                <input type="text" name="pin" class="form-control font-monospace" value="{{ $room->pin }}" placeholder="Leave blank for open">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label fw-semibold small text-muted">{{ __('Max Participants') }}</label>
                                                                <input type="number" name="max_participants" class="form-control" value="{{ $room->max_participants }}" min="2" max="300">
                                                            </div>
                                                        </div>
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" name="record" value="1" id="recSwitch{{ $room->id }}" {{ $room->record ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-semibold small" for="recSwitch{{ $room->id }}">{{ __('Record Conference Sessions') }}</label>
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enSwitch{{ $room->id }}" {{ $room->enabled ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-semibold small" for="enSwitch{{ $room->id }}">{{ __('Room Enabled') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Save Changes') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="mb-2">
                                        <i data-lucide="radio" style="width: 38px; height: 38px;" class="text-muted opacity-50"></i>
                                    </div>
                                    <div class="fw-semibold">{{ __('No Conference Rooms configured yet.') }}</div>
                                    <div class="small">{{ __('Click "+ Add Conference Room" to create your first team audio bridge.') }}</div>
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

<!-- Add Room Modal -->
<div class="modal fade" id="createRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.pbx.conferences.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="plus-circle" style="width: 18px; height: 18px;" class="text-primary"></i>
                        <span>{{ __('Create Team Bridge Room') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">{{ __('Room Number / Extension') }} *</label>
                        <input type="text" name="room_number" class="form-control font-monospace" placeholder="e.g. 703" required>
                        <div class="form-text fs-8">{{ __('Dialplan extension e.g. 700-799 that users dial to join.') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">{{ __('Conference Room Name') }} *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Marketing Strategy Bridge" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Purpose or department for this room"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">{{ __('Access PIN (Optional)') }}</label>
                            <input type="text" name="pin" class="form-control font-monospace" placeholder="e.g. 4821">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">{{ __('Max Capacity') }}</label>
                            <input type="number" name="max_participants" class="form-control" value="50" min="2" max="300">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="record" value="1" id="createRecSwitch">
                        <label class="form-check-label fw-semibold small" for="createRecSwitch">{{ __('Record Conference Sessions') }}</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="createEnSwitch" checked>
                        <label class="form-check-label fw-semibold small" for="createEnSwitch">{{ __('Room Enabled Immediately') }}</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">{{ __('Create Bridge Room') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function joinConferenceRoom(roomNumber, roomName) {
    if (typeof window.pbxCallContact === 'function') {
        window.pbxCallContact(roomNumber, roomName || ('Room ' + roomNumber));
    } else if (typeof window.togglePbxDialer === 'function') {
        window.togglePbxDialer();
        const input = document.getElementById('pbx-number-input');
        if (input) {
            input.value = roomNumber;
            if (typeof window.pbxCallDirect === 'function') {
                window.pbxCallDirect(roomNumber);
            }
        }
    }
}
</script>
@endsection
