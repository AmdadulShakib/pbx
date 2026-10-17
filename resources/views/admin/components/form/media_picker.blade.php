@php
    $name = $name ?? '';
    $inputName = $inputName ?? $name;
    $label = $label ?? $name;
    $required = $required ?? false;
    $column = $column ?? 'col-12';
    $inputId = $inputId ?? ($name . '_upload');
    $previewUrl = $previewUrl ?? '';
    $currentUrl = $currentUrl ?? $previewUrl;
    $help = $help ?? null;
    $isImage = $isImage ?? true;
    $removeName = $removeName ?? ('__remove_' . $name);
    $fieldKey = $fieldKey ?? $name;
    $errorKey = $errorKey ?? $name;
    $invalid = $errors->has($errorKey) ? 'is-invalid' : '';
    $hasPreview = is_string($currentUrl) && trim($currentUrl) !== '';
    $fileName = $hasPreview ? basename(parse_url($currentUrl, PHP_URL_PATH) ?? '') : '';
@endphp

<div class="{{ $column }}">
    <label class="form-label fw-semibold">
        @lang($label){!! $required ? ' <span class="text-danger">*</span>' : '' !!}
    </label>
    <div class="media-picker" data-media-picker data-field="{{ $fieldKey }}" data-is-image="{{ $isImage ? '1' : '0' }}" data-current-url="{{ $currentUrl }}">
        <div class="media-thumb">
            @if($isImage)
                <img class="media-preview js-image-preview {{ $hasPreview ? '' : 'd-none' }}" data-field="{{ $fieldKey }}" src="{{ $currentUrl }}" data-default="{{ displayImage('assets/images/default.jpg') }}" alt="Image">
            @else
                <div class="media-file-icon {{ $hasPreview ? 'd-none' : '' }}">
                    <i data-lucide="file-text"></i>
                </div>
                <a href="{{ $currentUrl }}" target="_blank" class="btn btn-link p-0 js-file-link {{ $hasPreview ? '' : 'd-none' }}" data-field="{{ $fieldKey }}">
                    @lang('View current file')
                </a>
            @endif
        </div>
        <div class="media-body">
            <div class="media-name text-muted small" data-file-name>{{ $fileName }}</div>
            @if(!empty($help))
                <div class="media-help small text-muted">{{ $help }}</div>
            @endif
            <div class="media-actions d-flex flex-wrap gap-2 mt-2 justify-content-end">
                <input type="file" name="{{ $inputName }}" class="d-none media-input {{ $invalid }}" id="{{ $inputId }}" data-media-field="{{ $fieldKey }}">
                <input type="hidden" name="{{ $removeName }}" value="0" data-remove-field="{{ $fieldKey }}">
                <button type="button" class="btn btn-outline-primary btn-sm media-upload" data-input="#{{ $inputId }}">@lang('Upload')</button>
                <button type="button" class="btn btn-outline-danger btn-sm media-remove {{ $hasPreview ? '' : 'd-none' }}" data-field="{{ $fieldKey }}">@lang('Remove')</button>
            </div>
        </div>
    </div>
</div>
