<?php

namespace App\Services\Pbx;

use App\Models\PbxCallLog;
use App\Models\PbxExtension;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsteriskCdrSyncService
{
    /**
     * Synchronize latest completed calls from Asterisk Master.csv into pbx_call_logs.
     */
    public function syncRecent(int $maxLines = 150): int
    {
        $csvContent = $this->fetchCsvContent($maxLines);
        if (empty($csvContent)) {
            return 0;
        }

        $lines = explode("\n", trim($csvContent));
        if (empty($lines)) {
            return 0;
        }

        // Cache extensions for quick name lookups
        $extensions = PbxExtension::with('user')->get()->keyBy('extension');

        $inserted = 0;
        // Process in reverse chronological order
        foreach (array_reverse($lines) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = str_getcsv($line);
            if (count($parts) < 15) continue;

            $accountCode = $parts[0] ?? '';
            $src         = trim($parts[1] ?? '');
            $dst         = trim($parts[2] ?? '');
            $context     = trim($parts[3] ?? '');
            $clid        = trim($parts[4] ?? '');
            $srcChannel  = trim($parts[5] ?? '');
            $dstChannel  = trim($parts[6] ?? '');
            $lastApp     = trim($parts[7] ?? '');
            $lastData    = trim($parts[8] ?? '');
            $startTime   = trim($parts[9] ?? '');
            $answerTime  = trim($parts[10] ?? '');
            $endTime     = trim($parts[11] ?? '');
            $duration    = (int) ($parts[12] ?? 0);
            $billsec     = (int) ($parts[13] ?? 0);
            $disposition = strtoupper(trim($parts[14] ?? ''));
            $amaflags    = $parts[15] ?? '';
            $uniqueId    = trim($parts[16] ?? '');

            if (empty($uniqueId) || empty($src) || empty($dst)) {
                continue;
            }

            // Skip internal ring group legs (Local channel dial sub-legs)
            if ($context === 'rg-member-dial') {
                continue;
            }

            // Skip if destination is hangup or internal echo test
            if (in_array($dst, ['999', '9999', '*43'], true) && $disposition !== 'ANSWERED') {
                continue;
            }

            // Check if this uniqueid already exists in database
            $exists = PbxCallLog::where('sip_call_id', $uniqueId)->exists();
            if ($exists) {
                continue;
            }

            // Map disposition
            $status = match ($disposition) {
                'ANSWERED'  => 'answered',
                'BUSY'      => 'busy',
                'NO ANSWER' => 'missed',
                'FAILED'    => 'failed',
                default     => strtolower($disposition),
            };

            // Caller and Callee details
            $callerExt = $extensions->get($src);
            $calleeExt = $extensions->get($dst);

            $callerName = $callerExt ? ($callerExt->user?->name ?? $callerExt->name) : null;
            $calleeName = $calleeExt ? ($calleeExt->user?->name ?? $calleeExt->name) : null;

            if (empty($callerName) && preg_match('/"([^"]+)"/', $clid, $m)) {
                $callerName = $m[1];
            }

            $userId = $callerExt?->user_id ?? $calleeExt?->user_id;

            $recordingFile = null;
            if ($status === 'answered' && $billsec > 0) {
                $recordingFile = "{$uniqueId}.wav";
            }

            $direction = 'internal';
            if ($context === 'from-trunk' || $context === 'incoming-routes') {
                $direction = 'inbound';
            } elseif (strlen($dst) > 7) {
                $direction = 'outbound';
            }

            $answeredBy = null;
            if ($status === 'answered') {
                $answeredBy = $calleeExt ? "Ext {$dst} ({$calleeName})" : "Ext {$dst}";
            }

            $startedAt = !empty($startTime) ? Carbon::parse($startTime) : now();
            $endedAt   = !empty($endTime) ? Carbon::parse($endTime) : now();

            try {
                PbxCallLog::create([
                    'user_id'        => $userId,
                    'caller'         => $src,
                    'caller_name'    => $callerName,
                    'callee'         => $dst,
                    'callee_name'    => $calleeName,
                    'answered_by'    => $answeredBy,
                    'direction'      => $direction,
                    'status'         => $status,
                    'duration'       => ($status === 'answered') ? max($billsec, $duration) : $duration,
                    'sip_call_id'    => $uniqueId,
                    'recording_file' => $recordingFile,
                    'started_at'     => $startedAt,
                    'ended_at'       => $endedAt,
                ]);
                $inserted++;
            } catch (\Throwable $e) {
                Log::debug("[CDR Sync] Insert error: " . $e->getMessage());
            }
        }

        return $inserted;
    }

    protected function fetchCsvContent(int $maxLines): ?string
    {
        // 1. Try local file if running on host
        $localPath = '/var/log/asterisk/cdr-csv/Master.csv';
        if (file_exists($localPath) && is_readable($localPath)) {
            $lines = @file($localPath);
            if ($lines !== false) {
                $slice = array_slice($lines, -$maxLines);
                return implode('', $slice);
            }
        }

        // 2. Try HTTP endpoint via internal reverse proxy
        $urls = [
            'https://pbx.103.107.160.37.sslip.io/internal-cdr/Master.csv',
            'http://10.0.1.1/internal-cdr/Master.csv',
            'http://127.0.0.1/internal-cdr/Master.csv',
        ];

        foreach ($urls as $url) {
            try {
                $res = Http::withoutVerifying()->timeout(2.5)->get($url);
                if ($res->successful() && !empty($res->body())) {
                    $body = $res->body();
                    $lines = explode("\n", $body);
                    $slice = array_slice($lines, -$maxLines);
                    return implode("\n", $slice);
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }
}
