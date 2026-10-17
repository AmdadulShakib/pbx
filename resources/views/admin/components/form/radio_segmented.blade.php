@php
    $name = $name ?? '';
    $inputName = $inputName ?? $name;
    $label = $label ?? $name;
    $options = $options ?? [];
    $value = $value ?? null;
    $required = $required ?? false;
    $column = $column ?? 'col-12 col-md-6';
    $useOld = $useOld ?? true;
    $idPrefix = $idPrefix ?? '';
    $help = $help ?? null;
    $oldKey = $oldKey ?? $name;
    $current = $useOld ? old($oldKey, $value) : $value;
    $current = is_null($current) ? '' : (string) $current;
    $twoTone = is_array($options) && count($options) === 2;
@endphp

<div class="{{ $column }}">
    <label class="form-label fw-semibold d-block">
        @lang($label){!! $required ? ' <span class="text-danger">*</span>' : '' !!}
    </label>
    <div class="segmented-buttons" role="group" aria-label="{{ $label }}">
        @foreach($options as $optionValue => $optionLabel)
            @php
                $optionValue = (string) $optionValue;
                $inputId = $idPrefix . $name . '_' . \Illuminate\Support\Str::slug($optionValue);
                $checked = $current !== '' && $current === $optionValue;
                $buttonClass = $twoTone
                    ? ($loop->first ? 'btn-outline-success' : 'btn-outline-danger')
                    : 'btn-outline-secondary';
            @endphp
            <input type="radio" class="btn-check" name="{{ $inputName }}" id="{{ $inputId }}" value="{{ $optionValue }}" {{ $checked ? 'checked' : '' }} {{ $required ? 'required' : '' }}>
            <label class="btn {{ $buttonClass }}" for="{{ $inputId }}">{{ ucwords($optionLabel) }}</label>
        @endforeach
    </div>
    @if(!empty($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
