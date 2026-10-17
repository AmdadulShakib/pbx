<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait FileUploadTrait
{
    /**
     * Upload image or file to public path and return stored file name.
     *
     * @throws \Exception
     */
    protected function uploadFile(string $type, array $data): string
    {
        $file = $data['file'] ?? null;
        if (!$file instanceof UploadedFile) {
            throw new Exception('Invalid file upload.');
        }

        $path = $data['path'] ?? 'uploads/';
        $path = trim($path, '/') . '/';
        $targetDir = public_path($path);

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $this->deletePreviousFile($path, $data['prevFile'] ?? null);

        $extension = $file->getClientOriginalExtension();
        $extension = $extension !== '' ? $extension : 'bin';
        $requestedName = $data['filename'] ?? null;
        if (is_string($requestedName) && $requestedName !== '') {
            $fileName = str_contains($requestedName, '.')
                ? $requestedName
                : $requestedName . '.' . $extension;
        } else {
            $fileName = Str::random(24) . '.' . $extension;
        }

        $file->move($targetDir, $fileName);

        return $fileName;
    }

    protected function deletePreviousFile(string $path, ?string $prevFile): void
    {
        if (!$prevFile) {
            return;
        }

        $prevFile = ltrim($prevFile, '/');
        $filePath = str_contains($prevFile, '/')
            ? public_path($prevFile)
            : public_path($path . $prevFile);

        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
