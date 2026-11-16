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
        $channels = $ami->getActiveChannels();
        $extensions = PbxExtension::with('user')->orderBy('extension')->get();

        // Detect default admin extension
        $currentAdminExt = null;
        if (auth('admin')->check()) {
            $adminUser = auth('admin')->user();
            $matched = $extensions->first(fn($e) => $e->user_id === $adminUser->id || $e->extension === '101');
            $currentAdminExt = $matched ? $matched->extension : ($extensions->first()?->extension ?? '101');
        } else {
            $currentAdminExt = $extensions->first()?->extension ?? '101';
        }

        return view('admin.pbx.live_calls', compact('channels', 'extensions', 'currentAdminExt'));
    }

    public function api(AsteriskAmi $ami)
    {
        $channels = $ami->getActiveChannels();

        return response()->json([
            'success'   => true,
            'count'     => count($channels),
            'channels'  => $channels,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function hangup(Request $request, AsteriskAmi $ami)
    {
        $request->validate([
            'channel' => ['required', 'string'],
        ]);

        $success = $ami->hangupChannel($request->input('channel'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? __('Channel terminated successfully.') : __('Failed to terminate channel.'),
            ]);
        }

        return back()->with($success ? 'ok' : 'err', $success ? __('Channel terminated successfully.') : __('Failed to terminate channel.'));
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
