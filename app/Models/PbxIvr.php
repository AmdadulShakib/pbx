<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PbxIvr extends Model
{
    protected $table = 'pbx_ivrs';

    protected $fillable = [
        'name',
        'extension',
        'description',
        'greeting_message',
        'sound_prompt',
        'timeout',
        'timeout_destination',
        'invalid_destination',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'timeout'   => 'integer',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(PbxIvrOption::class, 'ivr_id')->orderBy('digit');
    }

    /**
     * Generate Asterisk dialplan code snippet for this IVR menu
     */
    public function generateDialplanSnippet(): string
    {
        $ctx = "ivr-{$this->extension}";
        $out = "; ----------------------------------------\n";
        $out .= "; IVR: {$this->name} [Ext {$this->extension}]\n";
        $out .= "; ----------------------------------------\n";
        $out .= "[{$ctx}]\n";
        $out .= "exten => s,1,NoOp(Starting IVR: {$this->name} [Ext {$this->extension}])\n";
        $out .= " same => n,Answer()\n";
        $out .= " same => n,Wait(1)\n";
        $out .= " same => n,Set(TIMEOUT(digit)=3)\n";
        $out .= " same => n,Set(TIMEOUT(response)={$this->timeout})\n";
        $out .= " same => n,Playback(" . ($this->sound_prompt ?: 'beep') . ")\n";
        $out .= " same => n,WaitExten({$this->timeout})\n\n";

        foreach ($this->options as $opt) {
            $label = $opt->label ? " [{$opt->label}]" : "";
            $out .= "exten => {$opt->digit},1,NoOp(IVR [{$this->extension}] Option {$opt->digit} -> {$opt->destination}{$label})\n";
            $out .= " same => n,Goto(from-internal,{$opt->destination},1)\n";
        }

        // Timeout & Invalid
        $out .= "exten => t,1,NoOp(IVR [{$this->extension}] Timeout -> {$this->timeout_destination})\n";
        $out .= " same => n,Goto(from-internal,{$this->timeout_destination},1)\n";

        $out .= "exten => i,1,NoOp(IVR [{$this->extension}] Invalid -> {$this->invalid_destination})\n";
        $out .= " same => n,Goto(from-internal,{$this->invalid_destination},1)\n\n";

        return $out;
    }
}
