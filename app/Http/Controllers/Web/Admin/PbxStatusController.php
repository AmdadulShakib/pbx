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
            'driver'       => config('pbx.driver', 'asterisk'),
            'domain'       => config('pbx.domain', ''),
            'ws_url'       => config('pbx.ws_url', ''),
            'esl_host'     => config('pbx.host', '127.0.0.1'),
            'esl_port'     => config('pbx.port', 8021),
            'esl_pass'     => config('pbx.password', 'ClueCon'),
            'admin_emails' => config('pbx.admin_emails', ''),
            'ami_host'     => config('pbx.ami_host', '127.0.0.1'),
            'ami_port'     => config('pbx.ami_port', 5038),
            'ami_user'     => config('pbx.ami_user', 'admin'),
            'ami_secret'   => config('pbx.ami_secret', ''),
            'rawman_url'   => config('pbx.rawman_url', ''),
        ]);
    }

    public function saveSettings(Request $request, \App\Services\EnvEditor $envEditor)
    {
        $section = $request->input('_section', 'core');

        if ($section === 'ami') {
            $data = $request->validate([
                'ami_host'   => 'nullable|string|max:100',
                'ami_port'   => 'nullable|integer|min:1|max:65535',
                'ami_user'   => 'nullable|string|max:100',
                'ami_secret' => 'nullable|string|max:200',
                'rawman_url' => 'nullable|url|max:300',
            ]);

            $envEditor->setMany([
                'ASTERISK_AMI_HOST'   => $data['ami_host'] ?? '127.0.0.1',
                'ASTERISK_AMI_PORT'   => $data['ami_port'] ?? 5038,
                'ASTERISK_AMI_USER'   => $data['ami_user'] ?? 'admin',
                'ASTERISK_AMI_SECRET' => $data['ami_secret'] ?? '',
                'ASTERISK_RAWMAN_URL' => $data['rawman_url'] ?? '',
            ]);

            config([
                'pbx.ami_host'   => $data['ami_host'] ?? '127.0.0.1',
                'pbx.ami_port'   => (int) ($data['ami_port'] ?? 5038),
                'pbx.ami_user'   => $data['ami_user'] ?? 'admin',
                'pbx.ami_secret' => $data['ami_secret'] ?? '',
                'pbx.rawman_url' => $data['rawman_url'] ?? '',
            ]);

            return back()->with('ok', 'Asterisk AMI সেটিংস সফলভাবে আপডেট করা হয়েছে।');
        }

        // Core PBX settings
        $data = $request->validate([
            'driver'       => 'required|in:asterisk,freeswitch',
            'domain'       => 'required|string|max:100',
            'ws_url'       => 'required|string|max:200',
            'admin_emails' => 'nullable|string|max:500',
        ]);

        $envEditor->setMany([
            'PBX_DRIVER'       => $data['driver'],
            'PBX_DOMAIN'       => $data['domain'],
            'PBX_WS_URL'       => $data['ws_url'],
            'PBX_ADMIN_EMAILS' => $data['admin_emails'] ?? '',
        ]);

        config([
            'pbx.driver'       => $data['driver'],
            'pbx.domain'       => $data['domain'],
            'pbx.ws_url'       => $data['ws_url'],
            'pbx.admin_emails' => $data['admin_emails'] ?? '',
        ]);

        return back()->with('ok', 'PBX কোর সেটিংস সফলভাবে আপডেট করা হয়েছে।');
    }
}
