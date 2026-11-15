<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\PbxExtension;
use App\Services\Pbx\FreeSwitch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PbxStatusController extends Controller
{
    public function index(FreeSwitch $fs)
    {
        $driver     = config('pbx.driver', 'asterisk');
        $regs       = [];
        $error      = null;
        $extensions = PbxExtension::with('user')->orderBy('extension')->get();

        if ($driver === 'freeswitch') {
            try {
                $json = json_decode($fs->api('show registrations as json'), true);
                $regs = $json['rows'] ?? [];
            } catch (\Throwable $e) {
                $error = 'FreeSWITCH ESL: ' . $e->getMessage();
            }
        }

        return view('admin.pbx.status', compact('regs', 'error', 'driver', 'extensions'));
    }

    public function originate(Request $request, FreeSwitch $fs)
    {
        $d = $request->validate([
            'from' => ['required', 'digits_between:3,6'],
            'to'   => ['required', 'regex:/^[0-9*#+]{2,20}$/'],
        ]);

        $driver = config('pbx.driver', 'asterisk');
        if ($driver === 'freeswitch') {
            $cmd = 'originate {originate_timeout=30}user/' . $d['from'] . '@' . config('pbx.domain')
                 . ' ' . $d['to'] . ' XML default';

            try {
                $result = $fs->api($cmd, 40);
            } catch (\Throwable $e) {
                return back()->with('err', $e->getMessage());
            }

            return back()->with('ok', 'ফলাফল: ' . $result);
        }

        return back()->with('ok', "অরিজিনেট টেস্ট কল রিকোয়েস্ট প্রেরিত (Asterisk Ext: {$d['from']} -> {$d['to']})");
    }

    public function settings()
    {
        return view('admin.pbx.settings', [
            'driver'   => config('pbx.driver', 'asterisk'),
            'domain'   => config('pbx.domain', '10.211.10.238'),
            'ws_url'   => config('pbx.ws_url', 'ws://10.211.10.238:8088/ws'),
            'esl_host' => config('pbx.host', '127.0.0.1'),
            'esl_port' => config('pbx.port', 8021),
            'esl_pass' => config('pbx.password', 'ClueCon'),
        ]);
    }

    public function saveSettings(Request $request, \App\Services\EnvEditor $envEditor)
    {
        $data = $request->validate([
            'driver' => 'required|in:asterisk,freeswitch',
            'domain' => 'required|string|max:100',
            'ws_url' => 'required|string|max:200',
        ]);

        $envEditor->setMany([
            'PBX_DRIVER' => $data['driver'],
            'PBX_DOMAIN' => $data['domain'],
            'PBX_WS_URL' => $data['ws_url'],
        ]);

        config([
            'pbx.driver' => $data['driver'],
            'pbx.domain' => $data['domain'],
            'pbx.ws_url' => $data['ws_url'],
        ]);

        return back()->with('ok', 'PBX সার্ভার সেটিংস সফলভাবে আপডেট করা হয়েছে');
    }
}
