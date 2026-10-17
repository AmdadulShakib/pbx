<div class="empty-state text-center py-5">
    <div class="empty-icon mb-2">
        <i data-lucide="inbox"></i>
    </div>
    <div class="fw-semibold">{{ $message ?? __('No data found') }}</div>
    @if(!empty($helper))
        <div class="text-muted small mt-1">{{ $helper }}</div>
    @endif
    @if(!empty($action))
        <div class="mt-3">{!! $action !!}</div>
    @endif
</div>
