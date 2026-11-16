<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbxIvrOption extends Model
{
    protected $table = 'pbx_ivr_options';

    protected $fillable = [
        'ivr_id',
        'digit',
        'destination_type',
        'destination',
        'label',
    ];

    public function ivr(): BelongsTo
    {
        return $this->belongsTo(PbxIvr::class, 'ivr_id');
    }
}
