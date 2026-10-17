@extends('admin.layouts.app')

@section('title', __('Roles'))
@section('page-title', __('Roles & Permissions'))

@section('content')
    @php
        $actionPermissions = [
            'admin.roles.edit',
            'admin.roles.update',
            'admin.roles.destroy',
        ];
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.roles.edit')
                    || $user->can('admin.roles.update')
                    || $user->can('admin.roles.destroy'));
        }
        $colspan = $hasActionColumn ? 5 : 4;
    @endphp
    <div class="card table-card">
        <div class="card-header table-toolbar">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="fw-semibold">@lang('Manage Roles')</div>
                    <div class="text-muted small mt-1">@lang('Control admin access by assigning module permissions.')</div>
                </div>
                @if(auth('admin')->check() && (auth('admin')->user()->can('admin.roles.create') || auth('admin')->user()->can('admin.roles.store')))
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> @lang('Create Role')
                    </a>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;">@lang('Role')</th>
                        <th style="width: 15%;">@lang('Permissions')</th>
                        <th style="width: 15%;">@lang('Assigned')</th>
                        <th style="width: 15%;">@lang('Type')</th>
                        @if($hasActionColumn)
                            <th class="text-end" style="width: 15%;">@lang('Actions')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $role->name }}</div>
                                @if($role->description)
                                    <div class="text-muted small mt-1">{{ Str::limit($role->description, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft">{{ $role->permissions_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="badge badge-soft badge-soft-info">{{ $role->users_count ?? 0 }}</span>
                            </td>
                            <td>
                                @if($role->is_super)
                                    <span class="badge badge-soft badge-soft-success">
                                        <i class="fa fa-star me-1"></i> @lang('Super')
                                    </span>
                                @else
                                    <span class="badge badge-soft">@lang('Custom')</span>
                                @endif
                            </td>
                            @if($hasActionColumn)
                            <td class="text-end">
                                @if(auth('admin')->check() && (auth('admin')->user()->can('admin.roles.edit') || auth('admin')->user()->can('admin.roles.update')))
                                    <a href="{{ route('admin.roles.edit', $role) }}" 
                                       class="btn btn-icon btn-light btn-sm" 
                                       title="@lang('Edit')" 
                                       aria-label="@lang('Edit')">
                                        <i data-lucide="pencil" style="width: 16px; height: 16px;"></i>
                                    </a>
                                @endif
                                @if(auth('admin')->check() && auth('admin')->user()->can('admin.roles.destroy'))
                                    @if(!$role->is_super)
                                        <form method="post" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-icon btn-light btn-sm text-danger" 
                                                    type="submit" 
                                                    title="@lang('Delete')" 
                                                    aria-label="@lang('Delete')"
                                                    onclick="return confirm('@lang('Are you sure?')')">
                                                <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}" class="text-center text-muted py-5">
                                <div>
                                    <i data-lucide="inbox" style="width: 48px; height: 48px; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0">@lang('No roles created yet.')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
