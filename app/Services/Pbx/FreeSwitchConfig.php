<?php

namespace App\Services\Pbx;

use App\Models\PbxExtension;
use Illuminate\Support\Facades\File;

class FreeSwitchConfig
{
    public function __construct(private FreeSwitch $fs) {}

    public function sync(): void
    {
        $base  = config('pbx.conf_path');
        $dirs  = [$base . '/directory', $base . '/dialplan'];

        foreach ($dirs as $d) {
            File::ensureDirectoryExists($d);
            foreach (File::glob($d . '/*.xml') as $file) {
                File::delete($file);
            }
        }

        foreach (PbxExtension::where('enabled', true)->get() as $e) {
            File::put($dirs[0] . '/' . $e->extension . '.xml', $this->userXml($e));
            File::put($dirs[1] . '/' . $e->extension . '.xml', $this->dialplanXml($e));
        }

        try {
            $this->fs->api('reloadxml');
        } catch (\Throwable $e) {
            logger()->warning('FreeSWITCH reloadxml failed: ' . $e->getMessage());
        }
    }

    private function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function userXml(PbxExtension $e): string
    {
        $ext  = $this->esc($e->extension);
        $name = $this->esc($e->name);
        $pass = $this->esc($e->password);

        return <<<XML
<include>
  <user id="{$ext}">
    <params>
      <param name="password" value="{$pass}"/>
    </params>
    <variables>
      <variable name="user_context" value="default"/>
      <variable name="effective_caller_id_name" value="{$name}"/>
      <variable name="effective_caller_id_number" value="{$ext}"/>
      <variable name="outbound_caller_id_name" value="{$name}"/>
      <variable name="outbound_caller_id_number" value="{$ext}"/>
      <variable name="media_webrtc" value="true"/>
    </variables>
  </user>
</include>
XML;
    }

    private function dialplanXml(PbxExtension $e): string
    {
        $ext = $this->esc($e->extension);
        $tpl = '<include>
  <extension name="pbx-ext-__EXT__">
    <condition field="destination_number" expression="^__EXT__$">
      <action application="set" data="ringback=${us-ring}"/>
      <action application="set" data="instant_ringback=true"/>
      <action application="set" data="call_timeout=45"/>
      <action application="set" data="hangup_after_bridge=true"/>
      <action application="bridge" data="user/__EXT__@$${domain}"/>
    </condition>
  </extension>
</include>';

        return str_replace('__EXT__', $ext, $tpl);
    }
}