<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxConference extends Model
{
    use HasFactory;

    protected $table = 'pbx_conferences';

    protected $fillable = [
        'room_number',
        'name',
        'description',
        'pin',
        'max_participants',
        'record',
        'enabled',
    ];

    protected $casts = [
        'record'  => 'boolean',
        'enabled' => 'boolean',
    ];
}
