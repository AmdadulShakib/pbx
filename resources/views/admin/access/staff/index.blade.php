@extends('admin.layouts.app')

@section('title', __('Admin Staff'))
@section('page-title', __('Admin Staff'))

@section('content')
    @php
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.staff.edit')
                    || $user->can('admin.staff.update')
                    || $user->can('admin.staff.destroy'));
        }
        $colspan = $hasActionColumn ? 5 : 4;
    @endphp
    <div class="card table-card">
        <div class="card-header table-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="fw-semibold">@lang('Admin staff')</div>
                <div class="text-muted small">@lang('Manage admin employees and role assignments.')</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="get" class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" name="q" value="{{ $search }}" placeholder="@lang('Search staff...')">
                </form>
                @if(auth('admin')->check() && (auth('admin')->user()->can('admin.staff.create') || auth('admin')->user()->can('admin.staff.store')))
                    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">@lang('Add staff')</a>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Email')</th>
                        <th>@lang('Roles')</th>
                        <th>@lang('Created')</th>
                        @if($hasActionColumn)
                            <th class="text-end">@lang('Actions')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <td class="fw-semibold">{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>
                                @if(!empty($rolesEnabled))
                                    @forelse($member->roles as $role)
                                        <span class="badge badge-soft">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted">@lang('No roles')</span>
                                    @endforelse
                                @else
                                    <span class="text-muted">@lang('Roles unavailable')</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at?->format('M d, Y') }}</td>
                            @if($hasActionColumn)
                            <td class="text-end">
                                @if(auth('admin')->check() && (auth('admin')->user()->can('admin.staff.edit') || auth('admin')->user()->can('admin.staff.update')))
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-icon btn-light" title="@lang('Edit')" aria-label="@lang('Edit')">
                                        <i data-lucide="pencil"></i>
                                    </a>
                                @endif
                                @if(auth('admin')->check() && auth('admin')->user()->can('admin.staff.destroy'))
                                    <form method="post" action="{{ route('admin.staff.destroy', $member) }}" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-icon btn-outline-danger" title="@lang('Remove')" aria-label="@lang('Remove')">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}" class="text-center text-muted py-4">@lang('No staff members found.')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($staff, 'links'))
            <div class="card-footer bg-white">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
@endsection
