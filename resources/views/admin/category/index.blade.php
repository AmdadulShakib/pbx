@extends('admin.layouts.app')
@section('title', __('Category List'))
@section('page-title', __('Category'))
@section('content')
    @php
        $emptyAction = null;
        $filtersPanel = trim($__env->yieldPushContent('filters-panel'));
        $totalCount = method_exists($categories, 'total') ? $categories->total() : $categories->count();
        $title = __('Category List');
        $subtitle = __(':count total', ['count' => $totalCount]);
        $search = true;
        $actionPermissions = [
            'admin.category.edit',
            'admin.category.update',
            'admin.category.destroy',
        ];
        $hasActionColumn = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            $hasActionColumn = $user
                && ($user->can('admin.category.edit')
                    || $user->can('admin.category.update')
                    || $user->can('admin.category.destroy'));
        }
    @endphp
    @component('admin.components.table.card_table', compact('title', 'subtitle', 'search'))
        @slot('actions')
            <div class="d-flex align-items-center gap-2">
                @if(config('admin_ui.enable_bulk_actions', false))
                    @if(true)
                        @can('admin.category.destroy')
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bulk-action="delete" disabled>@lang('Delete Selected')</button>
                        @endcan
                    @endif
                    @if(false)
                        @can('admin.category.status')
                        <button type="button" class="btn btn-outline-warning btn-sm" data-bulk-action="status" disabled>@lang('Change Status')</button>
                        @endcan
                    @endif
                @endif
                @if($filtersPanel)
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersPanel" aria-expanded="true">@lang('Filters')</button>
                @endif
                
	@if(auth('admin')->check() && (auth('admin')->user()->can('admin.category.create') || auth('admin')->user()->can('admin.category.store')))
<a href="{{ route('admin.category.create') }}" class="btn btn-primary"><i data-lucide="plus" class="me-1"></i>@lang("Add Category")</a>
@endif

            </div>
        @endslot
        @slot('filters')
            @if($filtersPanel)
                <div class="card-body border-bottom">
                    <div class="collapse show" id="filtersPanel">
                        <div class="row g-2 align-items-end">
                            {!! $filtersPanel !!}
                            <div class="col-auto">
                                <button class="btn btn-outline-secondary btn-sm" type="button" data-filter-reset>@lang('Reset filters')</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endslot
        @slot('table')
            <div class="table-responsive">
                <table class="table table-premium table-hover align-middle mb-0 smart-table">
                    <thead class="table-light">
                        <tr>
                            @if(config('admin_ui.enable_bulk_actions', false))
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox" data-bulk-select-all>
                            </th>
                            @endif
                            <th class="text-muted">@lang('ID')</th>
                            
                            @if($hasActionColumn)
                                <th class="text-end">@lang('Action')</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $key => $category)
                            <tr>
                                @if(config('admin_ui.enable_bulk_actions', false))
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox" name="selected[]" value="{{ $category->id }}" data-bulk-row>
                                </td>
                                @endif
                                <td class="text-muted">{{ ++$key }}</td>
                                
                                
                                @if($hasActionColumn)
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1 action-buttons">
                                        @if(auth('admin')->check() && (auth('admin')->user()->can('admin.category.edit') || auth('admin')->user()->can('admin.category.update')))
<a href="{{ route('admin.category.edit', $category->id) }}" class="btn btn-icon btn-outline-info" data-bs-toggle="tooltip" title="@lang('Edit')"><i data-lucide="pencil"></i></a>
@endif
                                        @if(auth('admin')->check() && auth('admin')->user()->can('admin.category.destroy'))
<button class="btn btn-icon btn-outline-danger deleteBtn" type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-id="{{ $category->id }}"
                                            data-action="{{ route('admin.category.destroy', $category->id) }}">
                                            <span data-bs-toggle="tooltip" title="@lang('Delete')"><i data-lucide="trash-2"></i></span>
                                        </button>
@endif
                                        
                                    </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    @include('admin.partials.table_empty', [
                                        'message' => __('Data Not Found'),
                                        'helper' => __('Create your first Category to get started.'),
                                        'action' => $emptyAction,
                                    ])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endslot
        @slot('pagination')
            @php
                $canPaginate = is_object($categories) && $categories instanceof \Illuminate\Contracts\Pagination\Paginator;
            @endphp
            @if($canPaginate && $categories->hasPages())
                @include('admin.components.table.pagination', ['paginator' => $categories])
            @endif
        @endslot
    @endcomponent

    






{{-- Delete --}}
    <div id="deleteModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Category Delete Confirmation')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p>@lang('Are you sure to delete this category?')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn-danger">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('js')
	<script>
		(function($){
			"use strict";
			            $('.statusBtn').on('click', function () {
                var modal = $('#statusModal');
                modal.find('form').attr('action', $(this).data('action'));
                modal.find('input[name=id]').val($(this).data('id'));
            });

            $('.deleteBtn').on('click', function () {
                var modal = $('#deleteModal');
                modal.find('form').attr('action', $(this).data('action'));
                modal.find('input[name=id]').val($(this).data('id'));
            });

		})(jQuery);
	</script>
@endpush
