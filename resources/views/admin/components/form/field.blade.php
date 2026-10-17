@php
    $type = $type ?? 'text';
    $name = $name ?? '';
    $inputName = $inputName ?? $name;
    $label = $label ?? $name;
    $id = $id ?? $name;
    $required = $required ?? false;
    $column = $column ?? 'col-12 col-md-6';
    $useOld = $useOld ?? true;
    $value = $value ?? null;
    $placeholder = $placeholder ?? null;
    $options = $options ?? '';
    $multiple = $multiple ?? false;
    $selectClass = $selectClass ?? '';
    $attributes = $attributes ?? '';
    $inputClass = $inputClass ?? '';
    $raw = $raw ?? false;
    $help = $help ?? null;
    $oldKey = $oldKey ?? $name;
    $errorKey = $errorKey ?? $oldKey;
    $current = $useOld ? old($oldKey, $value) : $value;
    $displayValue = $current;
    if (is_array($displayValue)) {
        $displayValue = implode(', ', $displayValue);
    }
    $invalid = $errors->has($errorKey) ? 'is-invalid' : '';
@endphp

<div class="{{ $column }}">
    <label class="form-label fw-semibold" for="{{ $id }}">
        @lang($label){!! $required ? ' <span class="text-danger">*</span>' : '' !!}
    </label>

    @if($type === 'textarea')
        <textarea class="form-control form-control-lg {{ $inputClass }} {{ $invalid }}" name="{{ $inputName }}" id="{{ $id }}" rows="{{ $rows ?? 4 }}" placeholder="{{ $placeholder ?? '' }}" {{ $required ? 'required' : '' }} {!! $attributes !!}>@if($raw){!! $displayValue !!}@else{{ $displayValue }}@endif</textarea>
    @elseif($type === 'select')
        <select class="form-select form-select-lg {{ $selectClass }} {{ $inputClass }} {{ $invalid }}" name="{{ $multiple ? $inputName.'[]' : $inputName }}" id="{{ $id }}" {{ $multiple ? 'multiple' : '' }} {{ $required ? 'required' : '' }} data-placeholder="{{ $placeholder ?? __('Select One') }}" {!! $attributes !!}>
            @if(!$multiple)
                <option value="" disabled {{ empty($current) ? 'selected' : '' }}>@lang('Select One')</option>
            @endif
            {!! $options !!}
        </select>
    @else
        <input type="{{ $type }}" class="form-control form-control-lg {{ $inputClass }} {{ $invalid }}" name="{{ $inputName }}" id="{{ $id }}" value="{{ $displayValue }}" placeholder="{{ $placeholder ?? '' }}" {{ $required ? 'required' : '' }} {!! $attributes !!}>
    @endif

    @if(!empty($help))
        <div class="form-text">{{ $help }}</div>
    @endif

</div>
