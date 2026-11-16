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

        return view('admin.pbx.live_calls', compact('channels', 'extensions'));
    }

    public function api(AsteriskAmi $ami)
    {
        $channels = $ami->getActiveChannels();

        return response()->json([
            'success'  => true,
            'count'    => count($channels),
            'channels' => $channels,
            'timestamp'=> now()->toIso8601String(),
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
}
