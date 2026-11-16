<?php

namespace App\Services\Pbx;

use Illuminate\Support\Facades\Log;

class AsteriskAmi
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $secret;
    protected float $timeout;

    public function __construct()
    {
        $this->host     = config('pbx.ami_host', env('ASTERISK_AMI_HOST', '10.0.1.1'));
        $this->port     = (int) config('pbx.ami_port', env('ASTERISK_AMI_PORT', 5038));
        $this->username = config('pbx.ami_user', env('ASTERISK_AMI_USER', 'admin'));
        $this->secret   = config('pbx.ami_secret', env('ASTERISK_AMI_SECRET', 'voxpulse2026'));
        $this->timeout  = 2.0;
    }

    /**
     * Connect to AMI, execute action/command, and return response lines.
     */
    protected function execute(array $lines, int $readTimeoutMs = 1500): array
    {
        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$fp) {
            // Try localhost fallback if host was 10.0.1.1 and failed
            if ($this->host !== '127.0.0.1') {
                $fp = @fsockopen('127.0.0.1', $this->port, $errno, $errstr, 1.0);
            }
            if (!$fp) {
                Log::debug("Asterisk AMI connect failed: {$errstr} ({$errno})");
                return [];
            }
        }

        stream_set_timeout($fp, 2);
        // Read greeting
        $greeting = fgets($fp);

        // Login
        $loginCmd  = "Action: Login\r\n";
        $loginCmd .= "Username: {$this->username}\r\n";
        $loginCmd .= "Secret: {$this->secret}\r\n\r\n";
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
                $output[] = rtrim($line, "\r\n");
            }
            if ((microtime(true) - $startTime) * 1000 > $readTimeoutMs) {
                break;
            }
            if (in_array(trim($line ?? ''), ['--END COMMAND--', 'Response: Goodbye', ''], true) && count($output) > 2) {
                break;
            }
        }

        // Logoff
        @fwrite($fp, "Action: Logoff\r\n\r\n");
        @fclose($fp);

        return $output;
    }

    /**
     * Get active channels from Asterisk.
     * Returns structured array of live ongoing calls.
     */
    public function getActiveChannels(): array
    {
        $raw = $this->execute([
            'Action: Command',
            'Command: core show channels concise',
        ]);

        $channels = [];
        foreach ($raw as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, 'Response:') || str_starts_with($line, 'Privilege:') || str_starts_with($line, '--END') || str_starts_with($line, 'Output:')) {
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
                $uniqueId  = $parts[12] ?? '';

                // Extract caller from channel name if cid is empty
                if (empty($callerCid) && preg_match('#/([0-9]+)-#', $chanName, $m)) {
                    $callerCid = $m[1];
                }

                // Format state
                $stateLabel = match (strtolower($state)) {
                    'up' => 'Active / Connected',
                    'ring', 'ringing' => 'Ringing',
                    'dialing' => 'Dialing',
                    default => ucfirst($state),
                };

                $channels[] = [
                    'channel'    => $chanName,
                    'caller'     => $callerCid ?: 'Unknown',
                    'callee'     => $dialedExt ?: 'Internal',
                    'state'      => $stateLabel,
                    'raw_state'  => $state,
                    'app'        => $app,
                    'app_data'   => $appData,
                    'duration'   => $duration,
                    'formatted_duration' => sprintf('%02d:%02d', floor($duration / 60), $duration % 60),
                    'uniqueid'   => $uniqueId,
                ];
            }
        }

        return $channels;
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

        $res = $this->execute([
            'Action: Hangup',
            "Channel: {$channel}",
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
        $res = $this->execute([
            'Action: Originate',
            "Channel: PJSIP/{$from}",
            "Exten: {$to}",
            'Context: from-internal',
            'Priority: 1',
            "CallerID: {$from} <{$from}>",
            'Timeout: 30000',
        ]);

        foreach ($res as $line) {
            if (stripos($line, 'Response: Success') !== false) {
                return true;
            }
        }

        return false;
    }
}
