<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxExtension;
use App\Services\Pbx\AsteriskAmi;
use Illuminate\Http\Request;

class PbxLiveCallsController extends Controller
{
    public function index(AsteriskAmi $ami)
    {
        $channels = $this->enrichChannels($ami->getActiveChannels());
        $extensions = PbxExtension::with('user')->orderBy('extension')->get();

        // Detect default admin extension
        $currentAdminExt = null;
        if (auth('admin')->check()) {
            $adminUser = auth('admin')->user();
            $matched = $extensions->first(fn($e) => $e->user_id === $adminUser->id || $e->extension === '100' || $e->extension === '101');
            $currentAdminExt = $matched ? $matched->extension : ($extensions->first()?->extension ?? '100');
        } else {
            $currentAdminExt = $extensions->first()?->extension ?? '100';
        }

        return view('admin.pbx.live_calls', compact('channels', 'extensions', 'currentAdminExt'));
    }

    public function api(AsteriskAmi $ami)
    {
        $channels = $this->enrichChannels($ami->getActiveChannels());

        return response()->json([
            'success'   => true,
            'count'     => count($channels),
            'channels'  => $channels,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function hangup(Request $request, AsteriskAmi $ami)
    {
        $channel = $request->input('channel');
        $ext = $request->input('extension');

        $success = false;
        if ($ext) {
            $success = $ami->hangupExtension($ext);
        } elseif ($channel) {
            if (!str_contains($channel, '-')) {
                // Extension format passed e.g. PJSIP/100 or 100
                $cleanExt = preg_replace('/[^0-9]/', '', $channel);
                $success = $ami->hangupExtension($cleanExt);
            } else {
                $success = $ami->hangupChannel($channel);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? __('Call terminated successfully.') : __('Failed to terminate call.'),
            ]);
        }

        return back()->with($success ? 'ok' : 'err', $success ? __('Call terminated successfully.') : __('Failed to terminate call.'));
    }

    /**
     * Enrich active channel entries with user profile avatars and accurate names.
     */
    protected function enrichChannels(array $channels): array
    {
        if (empty($channels)) {
            return [];
        }

        $extensions = PbxExtension::with('user')->get()->keyBy('extension');
        $adminUser = auth('admin')->user();

        foreach ($channels as &$chan) {
            $callerExt = preg_replace('/[^0-9]/', '', $chan['caller'] ?? '');
            $calleeExt = preg_replace('/[^0-9]/', '', $chan['callee'] ?? '');

            // 1. Caller enrichment
            $chan['caller_avatar'] = null;
            if (!empty($callerExt) && ($extModel = $extensions->get($callerExt))) {
                $chan['caller_name'] = $extModel->name ?: ($extModel->user?->name ?? $chan['caller_name']);
                $chan['caller_avatar'] = $extModel->user?->avatar_url;
            } elseif ($callerExt === '100' && $adminUser) {
                $chan['caller_avatar'] = $adminUser->avatar_url;
                $chan['caller_name'] = $chan['caller_name'] ?: $adminUser->name;
            }

            // 2. Callee enrichment
            $chan['callee_avatar'] = null;
            if (!empty($calleeExt) && ($extModel = $extensions->get($calleeExt))) {
                $chan['callee_name'] = $extModel->name ?: ($extModel->user?->name ?? $chan['callee_name']);
                $chan['callee_avatar'] = $extModel->user?->avatar_url;
            } elseif ($calleeExt === '100' && $adminUser) {
                $chan['callee_avatar'] = $adminUser->avatar_url;
                $chan['callee_name'] = $chan['callee_name'] ?: $adminUser->name;
            }
        }
        unset($chan);

        return $channels;
    }

    public function originate(Request $request, AsteriskAmi $ami)
    {
        $request->validate([
            'from' => ['required', 'digits_between:2,8'],
            'to'   => ['required', 'string', 'max:30'],
        ]);

        $success = $ami->originateCall($request->input('from'), $request->input('to'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? __('Call originated successfully.') : __('Originate request failed.'),
            ]);
        }

        return back()->with($success ? 'ok' : 'err', $success ? __('Call originated successfully.') : __('Originate request failed.'));
    }

    public function spy(Request $request, AsteriskAmi $ami)
    {
        $request->validate([
            'admin_extension' => ['required', 'string'],
            'target'          => ['required', 'string'],
            'mode'            => ['required', 'in:listen,whisper,barge'],
        ]);

        $adminExt = $request->input('admin_extension');
        $target   = $request->input('target');
        $mode     = $request->input('mode');

        $success = $ami->spyChannel($adminExt, $target, $mode);

        $modeLabel = match ($mode) {
            'whisper' => __('Whisper Coaching'),
            'barge'   => __('3-Way Barge'),
            default   => __('Silent Monitoring'),
        };

        $msg = $success
            ? __(':mode initiated. Asterisk is ringing your softphone (Ext :ext). Answer to connect!', ['mode' => $modeLabel, 'ext' => $adminExt])
            : __('Failed to initiate monitoring session.');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $msg,
            ]);
        }

        return back()->with($success ? 'ok' : 'err', $msg);
    }
}
