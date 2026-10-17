<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbxExtension extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'password' => 'encrypted',
        'enabled'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
