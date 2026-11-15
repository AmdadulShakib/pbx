@extends('admin.layouts.app')
@section('title', __('PBX Extensions'))
@section('page-title', __('PBX Extensions'))

@section('content')
<div class="row g-3">
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

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                <div>
                    <h5 class="card-title mb-0 fw-bold">
                        <i data-lucide="phone" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
                        {{ __('Extension List') }}
                    </h5>
                    <small class="text-muted">{{ __('Manage internal employee extensions and SIP credentials') }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.pbx.status') }}" class="btn btn-outline-info btn-sm">
                        <i data-lucide="activity" class="me-1" style="width: 16px; height: 16px;"></i>
                        {{ __('PBX Live Status') }}
                    </a>
                    <a href="{{ route('admin.pbx.extensions.create') }}" class="btn btn-primary btn-sm">
                        <i data-lucide="plus" class="me-1" style="width: 16px; height: 16px;"></i>
                        {{ __('Add Extension') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">{{ __('Extension') }}</th>
                                <th>{{ __('Display Name') }}</th>
                                <th>{{ __('Assigned Employee') }}</th>
                                <th style="width: 120px;">{{ __('Status') }}</th>
                                <th style="width: 160px;">{{ __('Created At') }}</th>
                                <th style="width: 140px;" class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($extensions as $ext)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-1 fw-bold">
                                            {{ $ext->extension }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $ext->name }}</span>
                                    </td>
                                    <td>
                                        @if($ext->user)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-sm bg-light rounded-circle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    {{ strtoupper(substr($ext->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $ext->user->name }}</div>
                                                    <small class="text-muted">{{ $ext->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Unassigned') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ext->enabled)
                                            <span class="badge bg-success-subtle text-success">
                                                <i data-lucide="check" class="me-1" style="width: 12px; height: 12px;"></i>{{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i data-lucide="x" class="me-1" style="width: 12px; height: 12px;"></i>{{ __('Disabled') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $ext->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.pbx.extensions.edit', $ext) }}" class="btn btn-outline-secondary btn-sm" title="{{ __('Edit') }}">
                                                <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                                            </a>
                                            <form action="{{ route('admin.pbx.extensions.destroy', $ext) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this extension?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('Delete') }}">
                                                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i data-lucide="phone-off" class="mb-2 text-secondary" style="width: 40px; height: 40px;"></i>
                                            <h6>{{ __('No Extensions Found') }}</h6>
                                            <p class="small mb-3">{{ __('Get started by creating your first employee extension.') }}</p>
                                            <a href="{{ route('admin.pbx.extensions.create') }}" class="btn btn-primary btn-sm">
                                                <i data-lucide="plus" class="me-1"></i>{{ __('Create Extension') }}
                                            </a>
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
</div>
@endsection
