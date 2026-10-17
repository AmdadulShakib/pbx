@extends('admin.layouts.app')

@section('title', __('Users'))
@section('page-title', __('Users'))

@section('content')
    @php
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.users.edit')
                    || $user->can('admin.users.update')
                    || $user->can('admin.users.destroy'));
        }
        $colspan = $hasActionColumn ? 4 : 3;
    @endphp
    <div class="card table-card">
        <div class="card-header table-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="fw-semibold">@lang('Users')</div>
                <div class="text-muted small">@lang('Manage registered customers.')</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="get" class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" name="q" value="{{ $search }}" placeholder="@lang('Search users...')">
                </form>
                @if(auth('admin')->check() && (auth('admin')->user()->can('admin.users.create') || auth('admin')->user()->can('admin.users.store')))
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">@lang('Add user')</a>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-premium table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Email')</th>
                        <th>@lang('Created')</th>
                        @if($hasActionColumn)
                            <th class="text-end">@lang('Actions')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at?->format('M d, Y') }}</td>
                            @if($hasActionColumn)
                            <td class="text-end">
                                @if(auth('admin')->check() && (auth('admin')->user()->can('admin.users.edit') || auth('admin')->user()->can('admin.users.update')))
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light">@lang('Edit')</a>
                                @endif
                                @if(auth('admin')->check() && auth('admin')->user()->can('admin.users.destroy'))
                                    <form method="post" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">@lang('Remove')</button>
                                    </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}" class="text-center text-muted py-4">@lang('No users found.')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($users, 'links'))
            <div class="card-footer bg-white">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
