<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxInboundRoute extends Model
{
    use HasFactory;

    protected $table = 'pbx_inbound_routes';

    protected $fillable = [
        'name',
        'did_pattern',
        'caller_id_pattern',
        'destination_type',
        'destination_id',
        'cid_prefix',
        'priority',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'priority'  => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Generate Asterisk dialplan snippet for incoming DID routing in [from-trunk]
     */
    public function generateDialplan(): string
    {
        $did = trim($this->did_pattern);
        if ($did === '' || strtoupper($did) === 'ANY' || $did === '*') {
            $did = '_X.';
        } elseif (!str_starts_with($did, '_') && preg_match('/[XNZ]/i', $did)) {
            $did = '_' . $did;
        }

        $out = "; Inbound Route: {$this->name} (DID: {$this->did_pattern})\n";
        $out .= "exten => {$did},1,NoOp(Inbound Call on DID \${EXTEN} from \${CALLERID(num)})\n";

        // Prepend CID prefix if configured
        if ($this->cid_prefix) {
            $cleanPrefix = preg_replace('/[^a-zA-Z0-9\[\]\-_ ]/', '', $this->cid_prefix);
            $out .= " same => n,Set(CALLERID(name)={$cleanPrefix}\${CALLERID(name)})\n";
        }

        // Route to destination
        if ($this->destination_type === 'ivr') {
            $out .= " same => n,Goto(ivr-{$this->destination_id},s,1)\n";
        } elseif ($this->destination_type === 'ring_group') {
            $out .= " same => n,Goto(from-internal,{$this->destination_id},1)\n";
        } elseif ($this->destination_type === 'hangup') {
            $out .= " same => n,Hangup()\n";
        } else {
            // Extension
            $dest = $this->destination_id ?: '101';
            $out .= " same => n,Goto(from-internal,{$dest},1)\n";
        }

        return $out;
    }
}
