<?php

namespace App\Services\Pbx;

use App\Models\PbxInboundRoute;
use App\Models\PbxIvr;
use App\Models\PbxOutboundRoute;
use App\Models\PbxRingGroup;
use App\Models\PbxTrunk;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PbxRoutingSyncService
{
    protected AsteriskAmi $ami;

    public function __construct(AsteriskAmi $ami)
    {
        $this->ami = $ami;
    }

    /**
     * Generate complete dialplan snippet for extensions.conf
     */
    public function generateDialplanSnippet(): string
    {
        $ivrs           = PbxIvr::with('options')->where('is_active', true)->get();
        $ringGroups     = PbxRingGroup::where('is_active', true)->get();
        $inboundRoutes  = PbxInboundRoute::where('is_active', true)->orderBy('priority')->get();
        $outboundRoutes = PbxOutboundRoute::with(['primaryTrunk', 'failoverTrunk'])->where('is_active', true)->orderBy('priority')->get();

        $buf = "; ===================================================================\n";
        $buf .= "; VoxPulse Enterprise PBX - Compiled Routing Dialplan\n";
        $buf .= "; Generated at: " . date('Y-m-d H:i:s') . "\n";
        $buf .= "; ===================================================================\n\n";

        // 1. Ring Groups in [from-internal]
        $buf .= "; -------------------------------------------------------------------\n";
        $buf .= "; Ring Groups (Department Hunt / Simultaneous Groups)\n";
        $buf .= "; -------------------------------------------------------------------\n";
        if ($ringGroups->isEmpty()) {
            $buf .= "; (No active ring groups configured)\n\n";
        } else {
            foreach ($ringGroups as $rg) {
                $buf .= $rg->generateDialplan() . "\n";
            }
        }

        // 2. Outbound Routes in [from-internal]
        $buf .= "; -------------------------------------------------------------------\n";
        $buf .= "; Outbound Routes (PSTN / Carrier Trunk Dialing Rules)\n";
        $buf .= "; -------------------------------------------------------------------\n";
        if ($outboundRoutes->isEmpty()) {
            $buf .= "; (No active outbound routes configured)\n\n";
        } else {
            foreach ($outboundRoutes as $route) {
                $buf .= $route->generateDialplan() . "\n";
            }
        }

        // 3. Inbound Routes in [from-trunk]
        $buf .= "; -------------------------------------------------------------------\n";
        $buf .= "; Inbound Routes (Incoming DID & CallerID Match Rules)\n";
        $buf .= "; [from-trunk]\n";
        $buf .= "; -------------------------------------------------------------------\n";
        if ($inboundRoutes->isEmpty()) {
            $buf .= "; (No active inbound routes configured - fallback to 101)\n";
            $buf .= "exten => _X.,1,NoOp(Inbound Call with no matching route, routing to Ext 101)\n";
            $buf .= " same => n,Goto(from-internal,101,1)\n\n";
        } else {
            foreach ($inboundRoutes as $inbound) {
                $buf .= $inbound->generateDialplan() . "\n";
            }
            $buf .= "exten => s,1,NoOp(Catch-all fallback for unspecified DID)\n";
            $buf .= " same => n,Goto(from-internal,101,1)\n\n";
        }

        // 4. IVR Digital Receptionists
        $buf .= "; -------------------------------------------------------------------\n";
        $buf .= "; IVR Digital Receptionists\n";
        $buf .= "; -------------------------------------------------------------------\n";
        if ($ivrs->isEmpty()) {
            $buf .= "; (No active IVR menus configured)\n\n";
        } else {
            foreach ($ivrs as $ivr) {
                $buf .= $ivr->generateDialplanSnippet() . "\n";
            }
        }

        return $buf;
    }

    /**
     * Generate complete PJSIP snippet for SIP Trunks
     */
    public function generateTrunksSnippet(): string
    {
        $trunks = PbxTrunk::where('is_active', true)->get();

        $buf = "; ===================================================================\n";
        $buf .= "; VoxPulse Enterprise PBX - Compiled SIP Carrier Trunks\n";
        $buf .= "; Generated at: " . date('Y-m-d H:i:s') . "\n";
        $buf .= "; ===================================================================\n\n";

        if ($trunks->isEmpty()) {
            $buf .= "; (No active SIP trunks configured)\n";
        } else {
            foreach ($trunks as $trunk) {
                $buf .= $trunk->generatePjsipConfig();
            }
        }

        return $buf;
    }

    /**
     * Sync routing dialplan and reload Asterisk
     */
    public function syncAll(): array
    {
        $dialplanSnippet = $this->generateDialplanSnippet();
        $trunksSnippet   = $this->generateTrunksSnippet();

        $asteriskDir = env('ASTERISK_CONFIG_DIR', '/etc/asterisk');
        $routesFile  = "{$asteriskDir}/extensions_voxpulse_routes.conf";
        $trunksFile  = "{$asteriskDir}/pjsip_voxpulse_trunks.conf";

        $fileWriteOk = false;
        try {
            if (File::isDirectory($asteriskDir) && is_writable($asteriskDir)) {
                File::put($routesFile, $dialplanSnippet);
                File::put($trunksFile, $trunksSnippet);
                $fileWriteOk = true;
            }
        } catch (\Throwable $e) {
            Log::warning("Could not write Asterisk files directly: " . $e->getMessage());
        }

        // Execute AMI reloads
        $dialplanReloadOutput = $this->ami->command('dialplan reload');
        $pjsipReloadOutput    = $this->ami->command('pjsip reload');

        return [
            'file_written'     => $fileWriteOk,
            'dialplan_snippet' => $dialplanSnippet,
            'trunks_snippet'   => $trunksSnippet,
            'dialplan_output'  => implode("\n", $dialplanReloadOutput),
            'pjsip_output'     => implode("\n", $pjsipReloadOutput),
        ];
    }
}
