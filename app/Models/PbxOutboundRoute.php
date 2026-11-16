<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxOutboundRoute extends Model
{
    use HasFactory;

    protected $table = 'pbx_outbound_routes';

    protected $fillable = [
        'name',
        'dial_pattern',
        'strip_digits',
        'prepend_digits',
        'primary_trunk_id',
        'failover_trunk_id',
        'caller_id_override',
        'priority',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'strip_digits' => 'integer',
        'priority'     => 'integer',
        'is_active'    => 'boolean',
    ];

    public function primaryTrunk()
    {
        return $this->belongsTo(PbxTrunk::class, 'primary_trunk_id');
    }

    public function failoverTrunk()
    {
        return $this->belongsTo(PbxTrunk::class, 'failover_trunk_id');
    }

    /**
     * Generate Asterisk dialplan snippet for outgoing trunk dialing in [from-internal]
     */
    public function generateDialplan(): string
    {
        $pattern = trim($this->dial_pattern);
        if ($pattern === '') $pattern = '_X.';
        if (!str_starts_with($pattern, '_') && preg_match('/[XNZ\.]/i', $pattern)) {
            $pattern = '_' . $pattern;
        }

        $strip = max(0, intval($this->strip_digits));
        $prepend = trim($this->prepend_digits ?? '');

        // Determine dialed variable
        $dialVar = ($strip > 0) ? "\${EXTEN:{$strip}}" : "\${EXTEN}";
        if ($prepend !== '') {
            $dialVar = "{$prepend}{$dialVar}";
        }

        $out = "; Outbound Route: {$this->name} (Pattern: {$this->dial_pattern})\n";
        $out .= "exten => {$pattern},1,NoOp(Outbound Call via Route: {$this->name})\n";

        if ($this->caller_id_override) {
            $out .= " same => n,Set(CALLERID(num)={$this->caller_id_override})\n";
        }

        // Primary trunk
        if ($this->primaryTrunk) {
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($this->primaryTrunk->name));
            $endpoint = "trunk_{$safeName}";
            $out .= " same => n,Dial(PJSIP/{$dialVar}@{$endpoint},60,T)\n";
        }

        // Failover trunk if primary fails/busy
        if ($this->failoverTrunk) {
            $safeFailName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($this->failoverTrunk->name));
            $failEndpoint = "trunk_{$safeFailName}";
            $out .= " same => n,NoOp(Primary trunk failed with status \${DIALSTATUS}, trying failover trunk)\n";
            $out .= " same => n,Dial(PJSIP/{$dialVar}@{$failEndpoint},60,T)\n";
        }

        $out .= " same => n,Playtones(congestion)\n";
        $out .= " same => n,Congestion(5)\n";
        $out .= " same => n,Hangup()\n";

        return $out;
    }
}
