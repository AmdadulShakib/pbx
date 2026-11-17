<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxCallLog;
use App\Models\PbxExtension;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PbxCallLogController extends Controller
{
    public function index(Request $request)
    {
        $query = PbxCallLog::with('user')->latest();

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'missed') {
                $query->whereIn('status', ['missed', 'no-answer', 'busy', 'failed']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->input('direction'));
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('caller', 'like', "%{$term}%")
                  ->orWhere('callee', 'like', "%{$term}%")
                  ->orWhere('caller_name', 'like', "%{$term}%")
                  ->orWhere('callee_name', 'like', "%{$term}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Calculate Overview KPIs
        $totalCalls = PbxCallLog::count();
        $answeredCalls = PbxCallLog::where('status', 'answered')->count();
        $missedCalls = PbxCallLog::whereIn('status', ['missed', 'no-answer', 'busy', 'failed'])->count();
        $totalDurationSec = PbxCallLog::sum('duration') ?? 0;

        $hours = floor($totalDurationSec / 3600);
        $minutes = floor(($totalDurationSec % 3600) / 60);
        $seconds = $totalDurationSec % 60;
        $totalDurationFormatted = sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);

        return view('admin.pbx.calls.index', compact(
            'logs',
            'totalCalls',
            'answeredCalls',
            'missedCalls',
            'totalDurationFormatted'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'caller'         => ['required', 'string', 'max:50'],
            'callee'         => ['required', 'string', 'max:50'],
            'status'         => ['required', 'string', 'max:30'],
            'duration'       => ['nullable', 'integer', 'min:0'],
            'direction'      => ['nullable', 'string', 'in:inbound,outbound,internal'],
            'sip_call_id'    => ['nullable', 'string', 'max:150'],
            'answered_by'    => ['nullable', 'string', 'max:100'],
            'recording_file' => ['nullable', 'string', 'max:255'],
            'started_at'     => ['nullable', 'date'],
            'ended_at'       => ['nullable', 'date'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $callerExt = PbxExtension::with('user')->where('extension', $data['caller'])->first();
        $calleeExt = PbxExtension::with('user')->where('extension', $data['callee'])->first();

        $userId = null;
        if (auth()->guard('admin')->check()) {
            $userId = auth()->guard('admin')->id();
        } elseif (auth()->check()) {
            $userId = auth()->id();
        } elseif ($callerExt && $callerExt->user_id) {
            $userId = $callerExt->user_id;
        }

        $callerName = $callerExt ? ($callerExt->user?->name ?? $callerExt->name) : null;
        $calleeName = $calleeExt ? ($calleeExt->user?->name ?? $calleeExt->name) : null;

        $direction = $data['direction'] ?? 'internal';
        if (empty($direction)) {
            $direction = 'internal';
        }

        $status = strtolower($data['status']);
        $duration = (int)($data['duration'] ?? 0);

        // Derive answered_by if answered but not explicitly provided
        $answeredBy = $data['answered_by'] ?? null;
        if (empty($answeredBy) && $status === 'answered') {
            $answeredBy = $calleeExt ? "Ext {$calleeExt->extension} ({$calleeName})" : "Ext {$data['callee']}";
        }

        // Auto recording filename if call was answered and has duration
        $recordingFile = $data['recording_file'] ?? null;
        if (empty($recordingFile) && $status === 'answered' && $duration > 0) {
            $sipId = $data['sip_call_id'] ?? null;
            $recordingFile = $sipId ? "{$sipId}.wav" : "rec_" . date('Ymd_His') . "_{$data['caller']}_{$data['callee']}.wav";
        }

        $log = PbxCallLog::create([
            'user_id'        => $userId,
            'caller'         => $data['caller'],
            'caller_name'    => $callerName,
            'callee'         => $data['callee'],
            'callee_name'    => $calleeName,
            'answered_by'    => $answeredBy,
            'direction'      => $direction,
            'status'         => $status,
            'duration'       => $duration,
            'sip_call_id'    => $data['sip_call_id'] ?? null,
            'recording_file' => $recordingFile,
            'notes'          => $data['notes'] ?? null,
            'started_at'     => !empty($data['started_at']) ? Carbon::parse($data['started_at']) : now()->subSeconds($duration),
            'ended_at'       => !empty($data['ended_at']) ? Carbon::parse($data['ended_at']) : now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'log_id'  => $log->id,
            ]);
        }

        return back()->with('ok', 'Call record saved.');
    }

    public function recording(PbxCallLog $call)
    {
        $fileName = $call->recording_file;
        $searchDirs = [
            '/var/spool/asterisk/monitor',
            storage_path('app/recordings'),
            public_path('recordings'),
        ];

        $filePath = null;
        if (!empty($fileName)) {
            foreach ($searchDirs as $dir) {
                if (file_exists("{$dir}/{$fileName}")) {
                    $filePath = "{$dir}/{$fileName}";
                    break;
                }
            }
        }

        if (!$filePath && !empty($call->sip_call_id)) {
            foreach ($searchDirs as $dir) {
                if (file_exists("{$dir}/{$call->sip_call_id}.wav")) {
                    $filePath = "{$dir}/{$call->sip_call_id}.wav";
                    break;
                }
            }
        }

        if ($filePath && file_exists($filePath)) {
            return response()->file($filePath, [
                'Content-Type'        => 'audio/wav',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]);
        }

        // Return a dual-tone sample audio stream for demo/testing
        return $this->generateSampleAudioResponse($call);
    }

    protected function generateSampleAudioResponse(PbxCallLog $call)
    {
        $sampleRate = 8000;
        $seconds = min(max((int)$call->duration, 3), 10);
        $totalSamples = $sampleRate * $seconds;
        $dataSize = $totalSamples * 2;

        $header = pack(
            'A4VA4A4VvvVVvvA4V',
            'RIFF',
            36 + $dataSize,
            'WAVE',
            'fmt ',
            16,
            1,
            1,
            $sampleRate,
            $sampleRate * 2,
            2,
            16,
            'data',
            $dataSize
        );

        $samples = '';
        $freq1 = 440;
        $freq2 = 554.37;
        for ($i = 0; $i < $totalSamples; $i++) {
            $t = $i / $sampleRate;
            $cycle = fmod($t, 2.0);
            $vol = $cycle < 1.0 ? sin(M_PI * $cycle) * 0.35 : 0.05;
            $val = (sin(2 * M_PI * $freq1 * $t) + sin(2 * M_PI * $freq2 * $t)) * 0.5 * $vol;
            $sample16 = (int)($val * 32767);
            $samples .= pack('v', $sample16);
        }

        return response($header . $samples, 200, [
            'Content-Type'        => 'audio/wav',
            'Content-Disposition' => 'inline; filename="call_' . ($call->id) . '.wav"',
            'Cache-Control'       => 'public, max-age=3600',
        ]);
    }

    public function destroy(PbxCallLog $call)
    {
        $call->delete();
        return back()->with('ok', __('Call record removed successfully.'));
    }

    public function clearAll()
    {
        PbxCallLog::truncate();
        return back()->with('ok', __('All call records have been purged.'));
    }
}
