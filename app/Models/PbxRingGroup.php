<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxRingGroup extends Model
{
    use HasFactory;

    protected $table = 'pbx_ring_groups';

    protected $fillable = [
        'name',
        'extension',
        'strategy',
        'ring_time',
        'members',
        'failover_type',
        'failover_destination',
        'is_active',
        'description',
    ];

    protected $casts = [
        'ring_time' => 'integer',
        'members'   => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Generate Asterisk Dialplan snippet for this Ring Group
     */
    public function generateDialplan(): string
    {
        $ext = $this->extension;
        $ringTime = $this->ring_time ?: 20;
        $members = is_array($this->members) ? $this->members : [];

        $dialString = '';
        if ($this->strategy === 'hunt') {
            // Hunt (sequential ringing 1 by 1)
            $huntTime = max(5, intval($ringTime / max(1, count($members))));
            $step = 1;
            $out = "; Ring Group {$ext} (Hunt / Sequential)\n";
            $out .= "exten => {$ext},1,NoOp(Ring Group {$this->name} - Sequential)\n";
            $out .= " same => n,MixMonitor(/var/spool/asterisk/monitor/\${UNIQUEID}.wav,b)\n";
            foreach ($members as $m) {
                $m = trim($m);
                if (!$m) continue;
                $step++;
                $out .= " same => n,Dial(Local/{$m}@rg-member-dial,{$huntTime},r)\n";
            }
        } else {
            // ringall (simultaneous) or random
            $channels = [];
            foreach ($members as $m) {
                $m = trim($m);
                if ($m) $channels[] = "Local/{$m}@rg-member-dial";
            }
            $dialTargets = implode('&', $channels);

            $out = "; Ring Group {$ext} (Ring All)\n";
            $out .= "exten => {$ext},1,NoOp(Ring Group {$this->name} - Simultaneous)\n";
            $out .= " same => n,MixMonitor(/var/spool/asterisk/monitor/\${UNIQUEID}.wav,b)\n";
            if (!empty($dialTargets)) {
                $out .= " same => n,Dial({$dialTargets},{$ringTime},r)\n";
            }
        }

        // Failover handling
        $out .= " same => n,NoOp(Ring Group {$ext} unanswered, routing to failover)\n";
        if ($this->failover_type === 'ivr') {
            $out .= " same => n,Goto(ivr-{$this->failover_destination},s,1)\n";
        } elseif ($this->failover_type === 'hangup') {
            $out .= " same => n,Hangup()\n";
        } else {
            // extension / voicemail fallback
            $dest = $this->failover_destination ?: '101';
            $out .= " same => n,Goto(from-internal,{$dest},1)\n";
        }

        return $out;
    }
}
