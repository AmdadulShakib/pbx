<?php

namespace App\Services\Pbx;

use RuntimeException;

class FreeSwitch
{
    public function api(string $command, int $timeout = 5): string
    {
        $fp = @stream_socket_client(
            'tcp://' . config('pbx.host') . ':' . config('pbx.port'),
            $errno,
            $errstr,
            3
        );

        if (! $fp) {
            throw new RuntimeException("FreeSWITCH er sathe sonjog hoy ni: $errstr");
        }

        stream_set_timeout($fp, $timeout);

        try {
            $this->readFrame($fp);

            fwrite($fp, 'auth ' . config('pbx.password') . "\n\n");
            $auth = $this->readFrame($fp);
            if (! str_contains($auth['headers']['Reply-Text'] ?? '', '+OK')) {
                throw new RuntimeException('ESL password vule');
            }

            fwrite($fp, 'api ' . str_replace(["\r", "\n"], ' ', $command) . "\n\n");
            $res = $this->readFrame($fp);

            return trim($res['body']);
        } finally {
            fclose($fp);
        }
    }

    private function readFrame($fp): array
    {
        $headers = [];

        while (($line = fgets($fp)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                if ($headers) {
                    break;
                }
                continue;
            }
            [$key, $value] = array_pad(explode(': ', $line, 2), 2, '');
            $headers[$key] = $value;
        }

        $body = '';
        if (isset($headers['Content-Length'])) {
            $length = (int) $headers['Content-Length'];
            while (strlen($body) < $length) {
                $chunk = fread($fp, $length - strlen($body));
                if ($chunk === false || $chunk === '') {
                    break;
                }
                $body .= $chunk;
            }
        }

        return ['headers' => $headers, 'body' => $body];
    }
}