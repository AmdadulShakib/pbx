@extends('admin.layouts.app')
@section('title', __('Call History & CDR Records'))
@section('page-title', __('Call History'))

@section('content')
<div class="row g-3">
    <!-- Top Action Deck -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i data-lucide="history" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ __('Enterprise Call Detail Records (CDR)') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Audit-grade call telemetry, talk duration logs, and status records.') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5" onclick="window.location.reload();">
                        <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('Refresh Logs') }}</span>
                    </button>
                    @if($logs->total() > 0)
                        <form method="POST" action="{{ route('admin.pbx.calls.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to permanently purge all call records?') }}');" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-1.5">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                <span>{{ __('Purge CDR') }}</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Metrics -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Total Processed') }}</span>
                    <div class="rounded-2 bg-light text-primary p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="phone" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ number_format($totalCalls) }}</div>
                <div class="small text-muted">{{ __('All logged sessions') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Answered Calls') }}</span>
                    <div class="rounded-2 bg-success-subtle text-success p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-success">{{ number_format($answeredCalls) }}</div>
                <div class="small text-muted">{{ $totalCalls > 0 ? round(($answeredCalls / $totalCalls) * 100, 1) : 0 }}% {{ __('success rate') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Missed / Failed') }}</span>
                    <div class="rounded-2 bg-danger-subtle text-danger p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="phone-missed" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-danger">{{ number_format($missedCalls) }}</div>
                <div class="small text-muted">{{ __('Unanswered or dropped') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Total Talk Time') }}</span>
                    <div class="rounded-2 bg-info-subtle text-info p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark">{{ $totalDurationFormatted }}</div>
                <div class="small text-muted">{{ __('Accumulated voice duration') }}</div>
            </div>
        </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Status Filter Pills -->
                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                    <a href="{{ route('admin.pbx.calls.index') }}" class="btn btn-sm {{ !request('status') && !request('direction') ? 'btn-primary' : 'btn-outline-secondary' }} px-3 py-1.5">
                        {{ __('All Calls') }} ({{ $totalCalls }})
                    </a>
                    <a href="{{ route('admin.pbx.calls.index', ['status' => 'answered']) }}" class="btn btn-sm {{ request('status') === 'answered' ? 'btn-success' : 'btn-outline-secondary' }} px-3 py-1.5">
                        {{ __('Answered') }} ({{ $answeredCalls }})
                    </a>
                    <a href="{{ route('admin.pbx.calls.index', ['status' => 'missed']) }}" class="btn btn-sm {{ request('status') === 'missed' ? 'btn-danger' : 'btn-outline-secondary' }} px-3 py-1.5">
                        {{ __('Missed / Failed') }} ({{ $missedCalls }})
                    </a>
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('admin.pbx.calls.index') }}" class="d-flex align-items-center gap-2">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text border-end-0">
                            <i data-lucide="search" style="width: 14px; height: 14px;"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="{{ __('Search ext or employee...') }}" value="{{ request('search') }}">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.pbx.calls.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Clear') }}</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-custom-head text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 ps-md-4 py-3">{{ __('From (Caller)') }}</th>
                            <th class="py-3">{{ __('To (Destination)') }}</th>
                            <th class="py-3">{{ __('Answered By') }}</th>
                            <th class="py-3">{{ __('Direction') }}</th>
                            <th class="py-3">{{ __('Duration') }}</th>
                            <th class="py-3">{{ __('Status') }}</th>
                            <th class="py-3">{{ __('Recording') }}</th>
                            <th class="py-3">{{ __('Timestamp') }}</th>
                            <th class="text-end pe-3 pe-md-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $callerUser = $log->callerExtension?->user ?? $log->user;
                                $callerAvatar = $callerUser?->avatar_url ?? ($callerUser?->image ? asset($callerUser->image) : null);
                                $callerInitial = strtoupper(substr($log->caller_name ?: ($callerUser?->name ?: $log->caller), 0, 1));

                                $calleeUser = $log->calleeExtension?->user;
                                $calleeAvatar = $calleeUser?->avatar_url ?? ($calleeUser?->image ? asset($calleeUser->image) : null);
                                $calleeInitial = strtoupper(substr($log->callee_name ?: ($calleeUser?->name ?: $log->callee), 0, 1));
                            @endphp
                            <tr>
                                <td class="ps-3 ps-md-4 py-3">
                                    <div class="d-flex align-items-center gap-2.5">
                                        @if($callerAvatar)
                                            <img src="{{ $callerAvatar }}" alt="{{ $log->caller_name }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                            <div class="rounded-circle bg-primary-subtle text-primary border border-primary-subtle d-none d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px;">
                                                {{ $callerInitial }}
                                            </div>
                                        @else
                                            <div class="rounded-circle bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px;">
                                                {{ $callerInitial }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $log->caller_name ?: ('Ext ' . $log->caller) }}</div>
                                            <div class="text-muted small">
                                                <span class="badge bg-secondary-subtle text-secondary py-0.5 px-1.5 font-monospace" style="font-size: 10px;">Ext {{ $log->caller }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($calleeAvatar)
                                            <img src="{{ $calleeAvatar }}" alt="{{ $log->callee_name }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                            <div class="rounded-circle bg-info-subtle text-info border border-info-subtle d-none d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px;">
                                                {{ $calleeInitial }}
                                            </div>
                                        @else
                                            <div class="rounded-circle bg-info-subtle text-info border border-info-subtle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px;">
                                                {{ $calleeInitial }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ $log->callee_name ?: ('Ext ' . $log->callee) }}</div>
                                            <span class="badge bg-secondary-subtle text-secondary py-0.5 px-1.5 font-monospace" style="font-size: 10px;">Ext {{ $log->callee }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3">
                                    @if($log->status === 'answered')
                                        @php
                                            $ansUser = $log->calleeExtension?->user;
                                            $ansAvatar = $ansUser?->avatar_url ?? ($ansUser?->image ? asset($ansUser->image) : null);
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            @if($ansAvatar)
                                                <img src="{{ $ansAvatar }}" class="rounded-circle border border-success" style="width: 28px; height: 28px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-success-subtle text-success border border-success-subtle fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                                    <i data-lucide="user-check" style="width: 14px; height: 14px;"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark small">{{ $log->answered_by ?? ($log->callee_name ? 'Ext ' . $log->callee . ' (' . $log->callee_name . ')' : 'Ext ' . $log->callee) }}</div>
                                                <small class="text-success fw-medium" style="font-size: 10px;">{{ __('Answered') }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">{{ __('Not Answered') }}</span>
                                    @endif
                                </td>

                                <td class="py-3">
                                    @if($log->direction === 'inbound')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle d-inline-flex align-items-center gap-1 px-2 py-1">
                                            <i data-lucide="arrow-down-left" style="width: 12px; height: 12px;"></i>
                                            <span>{{ __('Inbound') }}</span>
                                        </span>
                                    @elseif($log->direction === 'outbound')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 px-2 py-1">
                                            <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i>
                                            <span>{{ __('Outbound') }}</span>
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border d-inline-flex align-items-center gap-1 px-2 py-1">
                                            <i data-lucide="refresh-cw" style="width: 11px; height: 11px;"></i>
                                            <span>{{ __('Internal') }}</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3">
                                    <span class="fw-medium font-monospace text-dark">{{ $log->formatted_duration }}</span>
                                </td>

                                <td class="py-3">
                                    @if($log->status === 'answered')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 px-2 py-1">
                                            <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                                            <span>{{ __('Answered') }}</span>
                                        </span>
                                    @elseif(in_array($log->status, ['missed', 'no-answer']))
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-inline-flex align-items-center gap-1 px-2 py-1">
                                            <i data-lucide="phone-missed" style="width: 12px; height: 12px;"></i>
                                            <span>{{ __('Missed') }}</span>
                                        </span>
                                    @elseif($log->status === 'busy')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle d-inline-flex align-items-center gap-1 px-2 py-1">
                                            <i data-lucide="slash" style="width: 12px; height: 12px;"></i>
                                            <span>{{ __('Line Busy') }}</span>
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle d-inline-flex align-items-center gap-1 px-2 py-1">
                                            <i data-lucide="x" style="width: 12px; height: 12px;"></i>
                                            <span>{{ ucfirst($log->status) }}</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3">
                                    @if($log->status === 'answered' && $log->duration > 0)
                                        <div class="d-flex align-items-center gap-1.5">
                                            <audio controls preload="none" style="height: 28px; width: 140px; max-width: 100%;">
                                                <source src="{{ route('admin.pbx.calls.recording', $log->id) }}" type="audio/wav">
                                            </audio>
                                            <a href="{{ route('admin.pbx.calls.recording', $log->id) }}" download="call_rec_{{ $log->id }}.wav" class="btn btn-sm btn-outline-secondary p-1" title="{{ __('Download Audio Recording') }}">
                                                <i data-lucide="download" style="width: 13px; height: 13px;"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                <td class="py-3 text-muted small">
                                    <div>{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-secondary">{{ $log->created_at->format('h:i:s A') }}</div>
                                </td>

                                <td class="text-end pe-3 pe-md-4 py-3">
                                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                        <!-- Quick Click-to-Dial -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success btn-action-icon" 
                                                onclick="if (typeof triggerPbxCall === 'function') triggerPbxCall('{{ $log->callee }}');" 
                                                title="{{ __('Call') }} {{ $log->callee }}" 
                                                data-bs-toggle="tooltip">
                                            <i data-lucide="phone-call"></i>
                                        </button>

                                        <!-- Delete Record -->
                                        <form method="POST" action="{{ route('admin.pbx.calls.destroy', $log->id) }}" onsubmit="return confirm('{{ __('Delete this log entry?') }}');" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger btn-action-icon" 
                                                    title="{{ __('Delete Record') }}" 
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
                                            <i data-lucide="phone-off" style="width: 32px; height: 32px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('No Call Records Found') }}</h6>
                                        <p class="text-muted small mb-0">{{ __('Calls initiated or received through the PBX Softphone will automatically log here.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="card-footer bg-transparent border-top py-3 px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="text-muted small">
                        {{ __('Showing :from to :to of :total call records', ['from' => $logs->firstItem() ?? 0, 'to' => $logs->lastItem() ?? 0, 'total' => $logs->total()]) }}
                    </div>
                    <div>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
