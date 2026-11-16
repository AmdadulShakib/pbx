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
}
