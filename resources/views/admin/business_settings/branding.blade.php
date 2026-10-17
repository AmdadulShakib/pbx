@extends('admin.layouts.app')
@section('title', __('Business Settings'))
@section('page-title', __('Business Settings'))
@section('content')
    @include('admin.business_settings._tabs')

    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.business-settings.branding.save') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        @php
                            $logoName = $logoUrl ? basename(parse_url($logoUrl, PHP_URL_PATH) ?? '') : __('No file selected');
                        @endphp
                        <div class="upload-dropzone border rounded-4 p-4 bg-body-tertiary shadow-sm h-100 d-flex flex-column" style="min-height: 220px;">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">
                                    <img id="branding_logo_preview"
                                         src="{{ $logoUrl ?? displayImage('assets/images/default.jpg') }}"
                                         data-default="{{ displayImage('assets/images/default.jpg') }}"
                                         alt="@lang('Logo preview')"
                                         class="rounded-4 img-thumbnail js-image-preview {{ $logoUrl ? '' : 'opacity-50' }}"
                                         style="width: 96px; height: 96px; object-fit: cover;"
                                         data-field="logo">
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 fw-semibold">@lang('Logo')</p>
                                    <p class="small text-muted mb-3">@lang('Ideal size: 200 x 200 px')</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <label class="btn btn-outline-primary btn-sm rounded-pill" for="branding_logo_upload">@lang('Upload')</label>
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm rounded-pill clear-image"
                                                data-preview="#branding_logo_preview"
                                                data-input="#branding_logo_upload">
                                            @lang('Remove')
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3">
                                <span class="small text-muted" data-file-name style="min-height: 18px;">{{ $logoName }}</span>
                                <span class="badge bg-secondary text-uppercase small">@lang('PNG, JPG, JPEG, WEBP, SVG')</span>
                            </div>
                        </div>
                        <input type="file"
                               name="logo"
                               class="d-none upload-image"
                               id="branding_logo_upload"
                               accept="image/png,image/jpg,image/jpeg,image/webp,image/svg+xml">
                    </div>
                    <div class="col-12 col-lg-6">
                        @php
                            $faviconName = $faviconUrl ? basename(parse_url($faviconUrl, PHP_URL_PATH) ?? '') : __('No file selected');
                            $isFaviconImage = $faviconExt === 'png';
                        @endphp
                        <div class="upload-dropzone border rounded-4 p-4 bg-body-tertiary shadow-sm h-100 d-flex flex-column" style="min-height: 220px;">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">
                                    @if($isFaviconImage)
                                        <img id="branding_favicon_preview"
                                             src="{{ $faviconUrl ?? displayImage('assets/images/default.jpg') }}"
                                             data-default="{{ displayImage('assets/images/default.jpg') }}"
                                             alt="@lang('Favicon preview')"
                                             class="rounded-circle img-thumbnail js-image-preview {{ $faviconUrl ? '' : 'opacity-50' }}"
                                             style="width: 64px; height: 64px; object-fit: cover;"
                                             data-field="favicon">
                                    @else
                                        <div class="bg-white border rounded-circle d-flex align-items-center justify-content-center" style="width:64px; height:64px;">
                                            <i data-lucide="image" class="text-muted fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 fw-semibold">@lang('Favicon')</p>
                                    <p class="small text-muted mb-3">@lang('ico or png, square 64 x 64')</p>
                                    <div class="d-flex gap-2">
                                        <label class="btn btn-outline-primary btn-sm rounded-pill" for="branding_favicon_upload">@lang('Upload')</label>
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm rounded-pill clear-image"
                                                data-preview="#branding_favicon_preview"
                                                data-input="#branding_favicon_upload">
                                            @lang('Remove')
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3">
                                <span class="small text-muted" data-file-name style="min-height: 18px;">{{ $faviconName }}</span>
                                <span class="badge bg-secondary text-uppercase small">@lang('ICO, PNG')</span>
                            </div>
                        </div>
                        <input type="file"
                               name="favicon"
                               class="d-none upload-image"
                               id="branding_favicon_upload"
                               accept="image/png,image/x-icon">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">@lang('Save Branding')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
