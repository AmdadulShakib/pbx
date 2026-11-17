<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxIvr;
use App\Models\PbxRingGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PbxRoutingController extends Controller
{
    /**
     * Asterisk Dialplan Dynamic Route Lookup.
     * Called via CURL() in extensions.conf for any extension not mapped statically.
     * Returns: type&targets&timeout&failover
     */
    public function lookup(Request $request): Response
    {
        $ext = trim($request->query('ext', ''));
        $caller = trim($request->query('caller', ''));

        if ($ext === '') {
            return response('', 200, ['Content-Type' => 'text/plain']);
        }

        // 1. Check Ring Group
        $ringGroup = PbxRingGroup::where('extension', $ext)
            ->where('is_active', true)
            ->first();

        if ($ringGroup) {
            $members = (array) $ringGroup->members;
            $targets = [];
            foreach ($members as $m) {
                $m = trim($m);
                if ($m !== '' && $m !== $caller) {
                    $targets[] = "Local/{$m}@rg-member-dial";
                }
            }

            if (empty($targets) && !empty($members)) {
                $first = trim($members[0]);
                if ($first !== '') {
                    $targets[] = "Local/{$first}@rg-member-dial";
                }
            }

            $dialTargets = implode('&', $targets);
            $timeout = max(5, min(120, (int) ($ringGroup->ring_time ?: 25)));
            $failover = '';

            if ($ringGroup->failover_type === 'extension' && !empty($ringGroup->failover_destination)) {
                $failover = "Local/{$ringGroup->failover_destination}@rg-member-dial";
            } elseif ($ringGroup->failover_type === 'ivr' && !empty($ringGroup->failover_destination)) {
                $failover = "Local/{$ringGroup->failover_destination}@internal-calls";
            }

            // Return: ringgroup|Local/101@rg-member-dial&Local/102@rg-member-dial|25|Local/101@rg-member-dial
            // Use pipe (|) delimiter so $dialTargets containing '&' is preserved as a single token!
            $result = "ringgroup|{$dialTargets}|{$timeout}|{$failover}";
            return response($result, 200, ['Content-Type' => 'text/plain']);
        }

        // 2. Check IVR Digital Receptionist
        $ivr = PbxIvr::where('extension', $ext)
            ->where('is_active', true)
            ->first();

        if ($ivr) {
            $context = "ivr-{$ivr->extension}";
            $result = "ivr|{$context}|0|";
            return response($result, 200, ['Content-Type' => 'text/plain']);
        }

        return response('', 200, ['Content-Type' => 'text/plain']);
    }
}
