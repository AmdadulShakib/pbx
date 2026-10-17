<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FileUploadTrait;
use App\Services\EnvEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BusinessSettingsController extends Controller
{
    use FileUploadTrait;
    public function index()
    {
        return redirect()->route('admin.business-settings.general');
    }

    public function general()
    {
        return view('admin.business_settings.general', [
            'appName' => config('app.name'),
            'appDebug' => (bool) config('app.debug'),
            'appUrl' => config('app.url'),
            'appTimezone' => config('app.timezone'),
        ]);
    }

    public function saveGeneral(Request $request, EnvEditor $envEditor)
    {
        $data = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_debug' => 'required|boolean',
            'app_url' => 'nullable|url',
            'app_timezone' => 'nullable|string|max:100',
        ]);

        $envEditor->setMany([
            'APP_NAME' => $data['app_name'],
            'APP_DEBUG' => (bool) $data['app_debug'],
            'APP_URL' => $data['app_url'] ?? '',
            'APP_TIMEZONE' => $data['app_timezone'] ?? '',
        ]);

        config([
            'app.name' => $data['app_name'],
            'app.debug' => (bool) $data['app_debug'],
            'app.url' => $data['app_url'] ?? config('app.url'),
            'app.timezone' => $data['app_timezone'] ?? config('app.timezone'),
        ]);

        return redirect()->back()
            ->with('success', 'Business settings updated successfully.')
            ->with('info', __('Some changes may require a refresh to take full effect.'));
    }

    public function branding()
    {
        $logo = $this->resolveBrandingAsset('logo', ['png', 'jpg', 'jpeg', 'webp', 'svg']);
        $favicon = $this->resolveBrandingAsset('favicon', ['ico', 'png']);

        return view('admin.business_settings.branding', [
            'logoUrl' => $logo['url'],
            'logoPath' => $logo['path'],
            'logoExt' => $logo['ext'],
            'faviconUrl' => $favicon['url'],
            'faviconPath' => $favicon['path'],
            'faviconExt' => $favicon['ext'],
        ]);
    }

    public function saveBranding(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,webp,svg',
            'favicon' => 'nullable|file|mimes:ico,png',
        ]);

        if ($request->hasFile('logo')) {
            $this->clearBrandingFiles(public_path('branding'), 'logo');
            $this->uploadFile('image', [
                'file' => $request->file('logo'),
                'path' => 'branding/',
                'filename' => 'logo',
            ]);
        }

        if ($request->hasFile('favicon')) {
            $this->clearBrandingFiles(public_path('branding'), 'favicon');
            $this->uploadFile('file', [
                'file' => $request->file('favicon'),
                'path' => 'branding/',
                'filename' => 'favicon',
            ]);
        }

        return redirect()->route('admin.business-settings.branding')
            ->with('success', 'Branding updated successfully.');
    }

    public function advanced()
    {
        return view('admin.business_settings.advanced');
    }

    protected function resolveBrandingAsset(string $baseName, array $extensions): array
    {
        foreach ($extensions as $ext) {
            $path = public_path("branding/{$baseName}.{$ext}");
            if (File::exists($path)) {
                return [
                    'path' => $path,
                    'url' => asset("branding/{$baseName}.{$ext}"),
                    'ext' => $ext,
                ];
            }
        }

        return [
            'path' => null,
            'url' => null,
            'ext' => null,
        ];
    }

    protected function clearBrandingFiles(string $dir, string $baseName): void
    {
        $pattern = rtrim($dir, '\\/') . '/' . $baseName . '.*';
        foreach (File::glob($pattern) as $file) {
            File::delete($file);
        }
    }
}

