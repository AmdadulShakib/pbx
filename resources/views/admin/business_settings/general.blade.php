@extends('admin.layouts.app')
@section('title', __('Business Settings'))
@section('page-title', __('Business Settings'))
@section('content')
    @include('admin.business_settings._tabs')

    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.business-settings.general.save') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">@lang('App Name') <span class="text-danger">*</span></label>
                        <input type="text" name="app_name" class="form-control" value="{{ old('app_name', $appName) }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">@lang('Debug Mode') <span class="text-danger">*</span></label>
                        <select name="app_debug" class="form-select" required>
                            <option value="1" @selected(old('app_debug', $appDebug ? '1' : '0') == '1')>@lang('Enabled')</option>
                            <option value="0" @selected(old('app_debug', $appDebug ? '1' : '0') == '0')>@lang('Disabled')</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">@lang('App URL')</label>
                        <input type="text" name="app_url" class="form-control" value="{{ old('app_url', $appUrl) }}" placeholder="https://example.com">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">@lang('Timezone')</label>
                        <input type="text" name="app_timezone" class="form-control" value="{{ old('app_timezone', $appTimezone) }}" placeholder="UTC">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">@lang('Save Settings')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
