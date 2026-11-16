<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxTrunk extends Model
{
    use HasFactory;

    protected $table = 'pbx_trunks';

    protected $fillable = [
        'name',
        'type',
        'host',
        'port',
        'transport',
        'username',
        'secret',
        'auth_user',
        'outbound_caller_id',
        'caller_name',
        'max_channels',
        'codecs',
        'is_registration',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'port'            => 'integer',
        'max_channels'    => 'integer',
        'is_registration' => 'boolean',
        'is_active'       => 'boolean',
    ];

    public function outboundRoutesAsPrimary()
    {
        return $this->hasMany(PbxOutboundRoute::class, 'primary_trunk_id');
    }

    public function outboundRoutesAsFailover()
    {
        return $this->hasMany(PbxOutboundRoute::class, 'failover_trunk_id');
    }

    /**
     * Generate Asterisk PJSIP configuration snippet for this trunk
     */
    public function generatePjsipConfig(): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($this->name));
        $endpoint = "trunk_{$safeName}";

        $out = "; ==========================================\n";
        $out .= "; Trunk: {$this->name} (ID: {$this->id})\n";
        $out .= "; ==========================================\n";

        if ($this->is_registration && $this->username && $this->secret) {
            $out .= "[reg_{$endpoint}]\n";
            $out .= "type=registration\n";
            $out .= "outbound_auth=auth_{$endpoint}\n";
            $out .= "server_uri=sip:{$this->host}:{$this->port}\n";
            $out .= "client_uri=sip:{$this->username}@{$this->host}:{$this->port}\n";
            $out .= "retry_interval=60\n";
            $out .= "expiration=3600\n\n";
        }

        if ($this->username && $this->secret) {
            $out .= "[auth_{$endpoint}]\n";
            $out .= "type=auth\n";
            $out .= "auth_type=userpass\n";
            $out .= "username=" . ($this->auth_user ?: $this->username) . "\n";
            $out .= "password={$this->secret}\n\n";
        }

        $out .= "[{$endpoint}]\n";
        $out .= "type=aor\n";
        $out .= "contact=sip:{$this->host}:{$this->port}\n\n";

        $out .= "[{$endpoint}]\n";
        $out .= "type=endpoint\n";
        $out .= "context=from-trunk\n";
        $out .= "disallow=all\n";
        foreach (explode(',', $this->codecs ?: 'ulaw,alaw,g729,opus') as $codec) {
            $c = trim($codec);
            if ($c) $out .= "allow={$c}\n";
        }
        $out .= "aors={$endpoint}\n";
        if ($this->username && $this->secret) {
            $out .= "outbound_auth=auth_{$endpoint}\n";
        }
        if ($this->outbound_caller_id) {
            $callerName = $this->caller_name ?: $this->name;
            $out .= "callerid=\"{$callerName}\" <{$this->outbound_caller_id}>\n";
        }
        $out .= "direct_media=no\n";
        $out .= "rtp_symmetric=yes\n";
        $out .= "force_rport=yes\n";
        $out .= "rewrite_contact=yes\n\n";

        return $out;
    }
}
