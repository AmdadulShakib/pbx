@props([
    'cancelUrl' => null,
    'cancelLabel' => __('Cancel'),
    'submitLabel' => __('Save'),
    'submitClass' => 'btn btn-primary',
    'showReset' => false,
    'resetLabel' => __('Reset'),
])

<div class="sticky-actions">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            @if($cancelUrl)
                <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary btn-sm">{{ $cancelLabel }}</a>
            @endif
            @if($showReset)
                <button type="reset" class="btn btn-outline-secondary btn-sm">{{ $resetLabel }}</button>
            @endif
        </div>
        <button type="submit" class="{{ $submitClass }}">{{ $submitLabel }}</button>
    </div>
</div>
