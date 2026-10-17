<?php

namespace App\Services;

class EnvEditor
{
    public function setMany(array $pairs): void
    {
        $envPath = base_path('.env');
        $content = '';

        if (is_file($envPath)) {
            $content = file_get_contents($envPath);
            if ($content === false) {
                $content = '';
            }
        }

        $lineEnding = str_contains($content, "\r\n") ? "\r\n" : "\n";
        $lines = $content === '' ? [] : preg_split("/\r\n|\n/", $content);

        $formatted = [];
        foreach ($pairs as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            $formatted[$key] = $this->formatValue($key, $value);
        }

        foreach ($lines as $index => $line) {
            if (preg_match('/^\s*([A-Z0-9_]+)\s*=/', $line, $matches)) {
                $key = $matches[1];
                if (array_key_exists($key, $formatted)) {
                    $lines[$index] = $key . '=' . $formatted[$key];
                    unset($formatted[$key]);
                }
            }
        }

        foreach ($formatted as $key => $value) {
            $lines[] = $key . '=' . $value;
        }

        $newContent = implode($lineEnding, $lines);
        if ($newContent !== '' && !str_ends_with($newContent, $lineEnding)) {
            $newContent .= $lineEnding;
        }

        file_put_contents($envPath, $newContent);
    }

    protected function formatValue(string $key, $value): string
    {
        if ($value === null) {
            $value = '';
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        $value = (string) $value;

        if ($key === 'APP_NAME') {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        if (preg_match('/\s/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }
}
