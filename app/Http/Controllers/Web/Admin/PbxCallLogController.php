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
            'caller'      => ['required', 'string', 'max:50'],
            'callee'      => ['required', 'string', 'max:50'],
            'status'      => ['required', 'string', 'max:30'],
            'duration'    => ['nullable', 'integer', 'min:0'],
            'direction'   => ['nullable', 'string', 'in:inbound,outbound,internal'],
            'sip_call_id' => ['nullable', 'string', 'max:150'],
            'started_at'  => ['nullable', 'date'],
            'ended_at'    => ['nullable', 'date'],
            'notes'       => ['nullable', 'string', 'max:500'],
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

        $log = PbxCallLog::create([
            'user_id'     => $userId,
            'caller'      => $data['caller'],
            'caller_name' => $callerName,
            'callee'      => $data['callee'],
            'callee_name' => $calleeName,
            'direction'   => $direction,
            'status'      => strtolower($data['status']),
            'duration'    => (int)($data['duration'] ?? 0),
            'sip_call_id' => $data['sip_call_id'] ?? null,
            'notes'       => $data['notes'] ?? null,
            'started_at'  => !empty($data['started_at']) ? Carbon::parse($data['started_at']) : now()->subSeconds((int)($data['duration'] ?? 0)),
            'ended_at'    => !empty($data['ended_at']) ? Carbon::parse($data['ended_at']) : now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'log_id'  => $log->id,
            ]);
        }

        return back()->with('ok', 'Call record saved.');
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
