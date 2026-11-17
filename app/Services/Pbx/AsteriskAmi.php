<?php

namespace App\Services\Pbx;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsteriskAmi
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $secret;
    protected float $timeout;
    protected ?string $rawmanUrl;

    public function __construct()
    {
        $this->host      = config('pbx.ami_host', env('ASTERISK_AMI_HOST', '10.0.1.1'));
        $this->port      = (int) config('pbx.ami_port', env('ASTERISK_AMI_PORT', 5038));
        $this->username  = config('pbx.ami_user', env('ASTERISK_AMI_USER', 'admin'));
        $this->secret    = config('pbx.ami_secret', env('ASTERISK_AMI_SECRET', 'voxpulse2026'));
        $this->rawmanUrl = config('pbx.rawman_url', env('ASTERISK_RAWMAN_URL', 'https://pbx.103.107.160.37.sslip.io/rawman'));
        $this->timeout   = 2.5;
    }

    /**
     * Send an AMI action and get array of response lines.
     * Tries rawman HTTP proxy first (cleanest across Docker/NAT), falls back to TCP.
     */
    public function query(array $params): array
    {
        // 1. Try HTTP rawman if configured
        if (!empty($this->rawmanUrl)) {
            $rawmanRes = $this->queryRawman($params);
            if (!empty($rawmanRes)) {
                return $rawmanRes;
            }
        }

        // 2. Fallback to TCP socket execute
        $lines = [];
        foreach ($params as $k => $v) {
            $lines[] = "{$k}: {$v}";
        }
        return $this->executeTcp($lines);
    }

    /**
     * Execute AMI request via Asterisk rawman HTTP endpoint.
     */
    protected function queryRawman(array $params, int $retry = 1): array
    {
        try {
            $sessionId = Cache::get('pbx_rawman_session_id');

            // If no session cached, log in first
            if (!$sessionId) {
                $loginUrl = $this->rawmanUrl . '?action=login&username=' . urlencode($this->username) . '&secret=' . urlencode($this->secret);
                $loginRes = Http::withoutVerifying()
                    ->timeout($this->timeout)
                    ->get($loginUrl);

                if ($loginRes->successful()) {
                    $setCookie = $loginRes->header('Set-Cookie') ?? '';
                    if (preg_match('/mansession_id="?([^";]+)"?/i', $setCookie, $m)) {
                        $sessionId = $m[1];
                        Cache::put('pbx_rawman_session_id', $sessionId, now()->addSeconds(45));
                    }
                }
            }

            // Prepare command query
            $queryString = http_build_query($params);
            $req = Http::withoutVerifying()->timeout($this->timeout);
            if ($sessionId) {
                $req = $req->withHeaders(['Cookie' => "mansession_id=\"{$sessionId}\""]);
            }

            $res = $req->get($this->rawmanUrl . '?' . $queryString);
            $body = $res->body();

            // Check if session expired / authentication failed
            if ((stripos($body, 'Permission denied') !== false || stripos($body, 'Authentication failed') !== false) && $retry > 0) {
                Cache::forget('pbx_rawman_session_id');
                return $this->queryRawman($params, $retry - 1);
            }

            return explode("\n", str_replace("\r", "", $body));
        } catch (\Throwable $e) {
            Log::debug("[PBX AMI] rawman error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Connect to AMI via TCP socket (fallback).
     */
    public function execute(array $lines, int $readTimeoutMs = 1500): array
    {
        return $this->executeTcp($lines, $readTimeoutMs);
    }

    protected function executeTcp(array $lines, int $readTimeoutMs = 1500): array
    {
        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$fp) {
            if ($this->host !== '127.0.0.1') {
                $fp = @fsockopen('127.0.0.1', $this->port, $errno, $errstr, 1.0);
            }
            if (!$fp) {
                Log::debug("[PBX AMI] TCP connect failed: {$errstr} ({$errno})");
                return [];
            }
        }

        stream_set_timeout($fp, 2);
        fgets($fp); // greeting

        // Login
        $loginCmd = "Action: Login\r\nUsername: {$this->username}\r\nSecret: {$this->secret}\r\n\r\n";
        fwrite($fp, $loginCmd);

        $loginOk = false;
        while (!feof($fp)) {
            $line = fgets($fp);
            if ($line === false || trim($line) === '') {
                break;
            }
            if (stripos($line, 'Response: Success') !== false) {
                $loginOk = true;
            }
        }

        if (!$loginOk) {
            fclose($fp);
            return [];
        }

        // Execute commands
        $cmdStr = implode("\r\n", $lines) . "\r\n\r\n";
        fwrite($fp, $cmdStr);

        $output = [];
        $startTime = microtime(true);
        while (!feof($fp)) {
            $line = fgets($fp);
            if ($line !== false) {
                $trimmed = rtrim($line, "\r\n");
                $output[] = $trimmed;
                if (str_contains($trimmed, '--END COMMAND--') || str_contains($trimmed, 'Response: Goodbye')) {
                    break;
                }
            }
            if ((microtime(true) - $startTime) * 1000 > $readTimeoutMs) {
                break;
            }
        }

        @fwrite($fp, "Action: Logoff\r\n\r\n");
        @fclose($fp);

        return $output;
    }

    /**
     * Run an arbitrary Asterisk CLI command via AMI
     */
    public function command(string $cliCommand): array
    {
        $raw = $this->query([
            'action'  => 'command',
            'command' => $cliCommand,
        ]);

        $lines = [];
        foreach ($raw as $line) {
            $line = trim($line);
            $cleaned = preg_replace('/^Output:\s*/i', '', $line);
            $cleaned = trim($cleaned);
            if ($cleaned !== '' && !str_starts_with($cleaned, 'Response:') && !str_starts_with($cleaned, 'Privilege:') && !str_starts_with($cleaned, '--END')) {
                $lines[] = $cleaned;
            }
        }
        return $lines;
    }

    /**
     * Get active channels from Asterisk.
     * Returns structured array of live ongoing calls.
     */
    public function getActiveChannels(): array
    {
        $raw = $this->query([
            'action'  => 'command',
            'command' => 'core show channels concise',
        ]);

        $channels = [];
        foreach ($raw as $line) {
            $line = trim($line);
            // Clean 'Output: ' prefix from Asterisk AMI command output
            $line = preg_replace('/^Output:\s*/i', '', $line);
            $line = trim($line);

            if ($line === '' || str_starts_with($line, 'Response:') || str_starts_with($line, 'Privilege:') || str_starts_with($line, 'Message:') || str_starts_with($line, '--END')) {
                continue;
            }

            // Asterisk concise format:
            // channel!context!exten!priority!state!appl!data!cid!accountcode!amaflags!duration!bridged!uniqueid
            $parts = explode('!', $line);
            if (count($parts) >= 5) {
                $chanName  = $parts[0] ?? '';
                $callerCid = $parts[7] ?? '';
                $dialedExt = $parts[2] ?? '';
                $state     = $parts[4] ?? 'Unknown';
                $app       = $parts[5] ?? '';
                $appData   = $parts[6] ?? '';
                $duration  = isset($parts[10]) ? (int)$parts[10] : 0;
                $bridged   = $parts[11] ?? '';
                $uniqueId  = $parts[12] ?? '';

                // Extract caller from channel name if cid is empty
                if (empty($callerCid) && preg_match('#/([0-9]+)-#', $chanName, $m)) {
                    $callerCid = $m[1];
                }

                // If dialed extension is empty or context default, extract from appData
                if ((empty($dialedExt) || $dialedExt === 's') && preg_match('#PJSIP/([0-9]+)#i', $appData, $m)) {
                    $dialedExt = $m[1];
                }

                // Format state
                $stateLabel = match (strtolower($state)) {
                    'up'              => 'Active / Connected',
                    'ring', 'ringing' => 'Ringing',
                    'dialing'         => 'Dialing',
                    default           => ucfirst($state),
                };

                $channels[] = [
                    'channel'            => $chanName,
                    'caller'             => $callerCid ?: 'Unknown',
                    'callee'             => $dialedExt ?: 'Internal',
                    'state'              => $stateLabel,
                    'raw_state'          => $state,
                    'app'                => $app,
                    'app_data'           => $appData,
                    'duration'           => $duration,
                    'formatted_duration' => sprintf('%02d:%02d', floor($duration / 60), $duration % 60),
                    'bridged'            => $bridged,
                    'uniqueid'           => $uniqueId,
                ];
            }
        }

        // Deduplicate bridged legs so 1 call (e.g. 101 -> 102) shows as 1 unified row
        $deduped = [];
        $processedChannels = [];

        foreach ($channels as $chan) {
            $name    = $chan['channel'];
            $bridged = $chan['bridged'];

            if (in_array($name, $processedChannels, true)) {
                continue;
            }

            // Skip internal Local channel legs if there's a bridged peer
            if (str_starts_with($name, 'Local/') && !empty($bridged)) {
                $processedChannels[] = $name;
                continue;
            }

            $processedChannels[] = $name;
            if (!empty($bridged)) {
                $processedChannels[] = $bridged;

                if (preg_match('#(?:PJSIP|Local)/([0-9]+)#i', $bridged, $bm) && ($chan['callee'] === 'Internal' || $chan['callee'] === $chan['caller'])) {
                    $chan['callee'] = $bm[1];
                }
            }

            $deduped[] = $chan;
        }

        return $deduped;
    }

    /**
     * Hangup a live active channel.
     */
    public function hangupChannel(string $channel): bool
    {
        $channel = trim($channel);
        if ($channel === '') {
            return false;
        }

        $res = $this->query([
            'action'  => 'Hangup',
            'channel' => $channel,
        ]);

        foreach ($res as $line) {
            if (stripos($line, 'Response: Success') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Originate a call between two extensions.
     */
    public function originateCall(string $from, string $to): bool
    {
        $res = $this->query([
            'action'   => 'Originate',
            'channel'  => "PJSIP/{$from}",
            'exten'    => $to,
            'context'  => 'internal-calls',
            'priority' => '1',
            'callerid' => "{$from} <{$from}>",
            'timeout'  => '30000',
            'async'    => 'true',
        ]);

        foreach ($res as $line) {
            if (stripos($line, 'Response: Success') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Supervisor ChanSpy: Listen (Silent), Whisper (Coach Agent), Barge (Join 3-way conference).
     *
     * @param string $adminExt Admin's extension (e.g. "101") where Asterisk will ring
     * @param string $target Target extension or channel (e.g. "103" or "PJSIP/103-00000002")
     * @param string $mode 'listen' (qud), 'whisper' (quwd), 'barge' (quBd)
     */
    public function spyChannel(string $adminExt, string $target, string $mode = 'listen'): bool
    {
        $adminExt = trim($adminExt);
        $target   = trim($target);

        if (empty($adminExt) || empty($target)) {
            return false;
        }

        // Determine ChanSpy options
        // q = quiet (no beeps), u = target is uniqueid/channel/prefix, d = DTMF mode switch (4:listen, 5:whisper, 6:barge)
        $flags = match (strtolower($mode)) {
            'whisper' => 'quwd',
            'barge'   => 'quBd',
            default   => 'qud', // silent listen
        };

        // Format target channel
        $chanTarget = str_starts_with($target, 'PJSIP/') ? $target : "PJSIP/{$target}";

        $res = $this->query([
            'action'      => 'Originate',
            'channel'     => "PJSIP/{$adminExt}",
            'application' => 'ChanSpy',
            'data'        => "{$chanTarget},{$flags}",
            'callerid'    => "Supervisor " . ucfirst($mode) . " <ChanSpy>",
            'async'       => 'true',
        ]);

        foreach ($res as $line) {
            if (stripos($line, 'Response: Success') !== false) {
                return true;
            }
        }

        return false;
    }
}
