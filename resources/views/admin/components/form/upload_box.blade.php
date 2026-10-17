@php
    $name = $name ?? '';
    $label = $label ?? $name;
    $required = $required ?? false;
    $column = $column ?? 'col-12';
    $inputId = $inputId ?? ($name . '_upload');
    $previewId = $previewId ?? ($name . '_preview');
    $previewUrl = $previewUrl ?? '';
    $isImage = $isImage ?? true;
    $help = $help ?? null;
    $mimes = $mimes ?? '*';
    $size = $size ?? 'N/A';
    $invalid = $errors->has($name) ? 'is-invalid' : '';
    $hasPreview = is_string($previewUrl) && trim($previewUrl) !== '';
@endphp

<div class="{{ $column }}">
    <label class="form-label fw-semibold">
        @lang($label){!! $required ? ' <span class="text-danger">*</span>' : '' !!}
    </label>
    <label class="upload-dropzone w-100">
        <input type="file" name="{{ $name }}" class="d-none upload-image {{ $invalid }}" id="{{ $inputId }}">
        <div class="d-flex flex-column align-items-center gap-2">
            @if($isImage)
                <img id="{{ $previewId }}" src="{{ $previewUrl }}" data-default="{{ displayImage('assets/images/default.jpg') }}" alt="Image" class="img-thumbnail js-image-preview {{ $hasPreview ? '' : 'd-none' }}" data-field="{{ $name }}" style="max-width: 160px;">
            @else
                <a href="{{ $previewUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm js-file-link {{ $hasPreview ? '' : 'd-none' }}" data-field="{{ $name }}">@lang('View current file')</a>
            @endif
            <div class="text-muted small">@lang('Click to upload')</div>
        </div>
    </label>
    <div class="small text-muted mt-2" data-file-name></div>
    <button type="button" class="btn btn-sm btn-outline-secondary mt-2 clear-image" data-preview="#{{ $previewId }}" data-input="#{{ $inputId }}">@lang('Remove')</button>
    <small class="text-muted d-block text-center">Supported files: {{ $mimes }}. Image will be re-size into: {{ $size }}px</small>
    @if(!empty($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
