@php
    $isPaginator = is_object($paginator) && $paginator instanceof \Illuminate\Contracts\Pagination\Paginator;
@endphp
@if($isPaginator && $paginator->hasPages())
    {{ $paginator->links('admin.components.table.pagination_view') }}
@endif
