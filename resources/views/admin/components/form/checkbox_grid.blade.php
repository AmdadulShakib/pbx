@php
    $name = $name ?? '';
    $inputName = $inputName ?? $name;
    $label = $label ?? $name;
    $options = $options ?? [];
    $value = $value ?? [];
    $column = $column ?? 'col-12 col-md-6';
    $useOld = $useOld ?? true;
    $idPrefix = $idPrefix ?? '';
    $help = $help ?? null;
    $oldKey = $oldKey ?? $name;
    $current = $useOld ? old($oldKey, $value) : $value;
    if (is_string($current)) {
        $decoded = json_decode($current, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $current = $decoded;
        } else {
            $current = $current === '' ? [] : explode(',', $current);
        }
    }
    $current = is_array($current) ? $current : (is_null($current) ? [] : [$current]);
@endphp

<div class="{{ $column }}">
    <label class="form-label fw-semibold d-block">@lang($label)</label>
    <div class="checkbox-grid">
        @foreach($options as $optionValue => $optionLabel)
            @php
                $optionValue = (string) $optionValue;
                $inputId = $idPrefix . $name . '_' . \Illuminate\Support\Str::slug($optionValue);
                $checked = in_array($optionValue, $current, true);
            @endphp
            <input class="btn-check" type="checkbox" name="{{ $inputName }}[]" id="{{ $inputId }}" value="{{ $optionValue }}" {{ $checked ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary" for="{{ $inputId }}">{{ ucwords($optionLabel) }}</label>
        @endforeach
    </div>
    @if(!empty($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
