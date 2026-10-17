<?php

namespace App\Http\Controllers\Web\Admin;

use App\Services\Pbx\FreeSwitch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PbxStatusController extends Controller
{
    public function index(FreeSwitch $fs)
    {
        $regs  = [];
        $error = null;

        try {
            $json = json_decode($fs->api('show registrations as json'), true);
            $regs = $json['rows'] ?? [];
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('pbx.status', compact('regs', 'error'));
    }

    public function originate(Request $request, FreeSwitch $fs)
    {
        $d = $request->validate([
            'from' => ['required', 'digits_between:3,6'],
            'to'   => ['required', 'regex:/^[0-9*#+]{2,20}$/'],
        ]);

        $cmd = 'originate {originate_timeout=30}user/' . $d['from'] . '@' . config('pbx.domain')
             . ' ' . $d['to'] . ' XML default';

        try {
            $result = $fs->api($cmd, 40);
        } catch (\Throwable $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', 'ফলাফল: ' . $result);
    }
}
