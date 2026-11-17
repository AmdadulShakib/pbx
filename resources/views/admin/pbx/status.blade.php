@extends('admin.layouts.app')
@section('title', __('PBX Live Status'))
@section('page-title', __('PBX Live Status'))

@section('content')
<div class="row g-4">
    <div class="col-12">
        @if(session('ok'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i data-lucide="check-circle" class="me-2"></i>{{ session('ok') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('err'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i data-lucide="alert-triangle" class="me-2"></i>{{ session('err') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($error)
            <div class="alert alert-warning border-warning shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i data-lucide="alert-octagon" class="text-warning me-2" style="width: 24px; height: 24px;"></i>
                    <div>
                        <h6 class="alert-heading mb-1 fw-bold">{{ __(ucfirst($driver) . ' Service Unreachable') }}</h6>
                        <p class="mb-0 small">{{ $error }}</p>
                        <small class="text-muted">{{ __('Please verify your PBX backend service status and configuration.') }}</small>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Live Registrations / Extensions Card -->
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0 fw-bold">
                        <i data-lucide="radio" class="me-2 text-success" style="width: 20px; height: 20px;"></i>
                        {{ __('PBX Extensions & Registrations') }}
                    </h5>
                    <small class="text-muted">
                        {{ __('Engine:') }} <strong class="text-primary text-uppercase">{{ $driver }}</strong> | 
                        {{ __('WebSocket:') }} <code>{{ config('pbx.ws_url') }}</code>
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pbx.status') }}" class="btn btn-outline-secondary btn-sm">
                        <i data-lucide="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>
                        {{ __('Refresh') }}
                    </a>
                    <a href="{{ route('admin.pbx.extensions.index') }}" class="btn btn-outline-primary btn-sm">
                        <i data-lucide="users" class="me-1" style="width: 14px; height: 14px;"></i>
                        {{ __('Manage Extensions') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if($driver === 'asterisk' && empty($regs))
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-custom-head text-muted small text-uppercase">
                                <tr>
                                    <th>{{ __('Extension') }}</th>
                                    <th>{{ __('Display Name') }}</th>
                                    <th>{{ __('Assigned Employee') }}</th>
                                    <th>{{ __('Protocol') }}</th>
                                    <th class="text-end">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($extensions as $ext)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary fs-6 fw-bold px-2.5 py-1">
                                                Ext {{ $ext->extension }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $ext->name }}</span>
                                        </td>
                                        <td>
                                            @if($ext->user)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-xs rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 11px;">
                                                        {{ strtoupper(substr($ext->user->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="small fw-semibold text-dark">{{ $ext->user->name }}</div>
                                                        <div class="text-muted" style="font-size: 11px;">{{ $ext->user->email }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary small">{{ __('Unassigned') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">WebRTC / WSS</span>
                                        </td>
                                        <td class="text-end">
                                            @if($ext->enabled)
                                                <span class="badge bg-success">
                                                    <i data-lucide="check" style="width: 12px; height: 12px;" class="me-1"></i>{{ __('Enabled') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Disabled') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i data-lucide="phone-off" class="mb-2 text-secondary" style="width: 36px; height: 36px;"></i>
                                            <p class="mb-0">{{ __('No extensions created yet.') }}</p>
                                            <a href="{{ route('admin.pbx.extensions.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i data-lucide="plus" class="me-1" style="width: 14px; height: 14px;"></i>{{ __('Create First Extension') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-custom-head text-muted small text-uppercase">
                                <tr>
                                    <th>{{ __('User / Extension') }}</th>
                                    <th>{{ __('Contact IP / Port') }}</th>
                                    <th>{{ __('Agent') }}</th>
                                    <th>{{ __('Expires') }}</th>
                                    <th class="text-end">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($regs as $r)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success-subtle text-success fs-6 fw-bold px-2 py-1">
                                                    {{ $r['reg_user'] ?? 'N/A' }}
                                                </span>
                                                <span class="text-muted small">@ {{ $r['realm'] ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="small text-dark">{{ $r['network_ip'] ?? '' }}:{{ $r['network_port'] ?? '' }}</code>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block small" style="max-width: 160px;" title="{{ $r['agent'] ?? '' }}">
                                                {{ $r['agent'] ?? 'WebRTC' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small text-muted">{{ $r['expires'] ?? '' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-success">
                                                <i data-lucide="check" style="width: 12px; height: 12px;" class="me-1"></i>{{ __('Registered') }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i data-lucide="phone-off" class="mb-2 text-secondary" style="width: 36px; height: 36px;"></i>
                                            <p class="mb-0">{{ __('No extensions currently registered online.') }}</p>
                                            <small>{{ __('When an employee opens the page and registers their dialer, their extension will appear here.') }}</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Originate Test Call Card -->
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i data-lucide="phone-outgoing" class="me-2 text-primary" style="width: 18px; height: 18px;"></i>
                    {{ __('Server Test Call (Originate)') }}
                </h6>
                <small class="text-muted">{{ __('Test bridging two extensions from FreeSWITCH ESL') }}</small>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('admin.pbx.originate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="from" class="form-label small fw-semibold">{{ __('From Extension') }}</label>
                        <input type="text" class="form-control form-control-sm" id="from" name="from" placeholder="e.g. 1001" required>
                    </div>
                    <div class="mb-3">
                        <label for="to" class="form-label small fw-semibold">{{ __('To Destination / Extension') }}</label>
                        <input type="text" class="form-control form-control-sm" id="to" name="to" placeholder="e.g. 1002" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i data-lucide="phone-forwarded" class="me-1" style="width: 14px; height: 14px;"></i>
                        {{ __('Originate Test Call') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Connection Info Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i data-lucide="server" class="me-2 text-secondary" style="width: 18px; height: 18px;"></i>
                    {{ __('PBX Server Details') }}
                </h6>
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">{{ __('ESL Host') }}</span>
                    <span class="fw-semibold">{{ config('pbx.host') }}:{{ config('pbx.port') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">{{ __('PBX Domain') }}</span>
                    <span class="fw-semibold">{{ config('pbx.domain') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">{{ __('WebRTC WS URL') }}</span>
                    <span class="fw-semibold text-break">{{ config('pbx.ws_url') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">{{ __('Config Path') }}</span>
                    <span class="fw-semibold text-break">{{ config('pbx.conf_path') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
