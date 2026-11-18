<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbxHotDesk extends Model
{
    use HasFactory;

    protected $table = 'pbx_hot_desks';

    protected $fillable = [
        'name',
        'station_endpoint',
        'device_model',
        'mac_address',
        'ip_address',
        'location',
        'current_extension_id',
        'current_extension',
        'current_user_name',
        'pin',
        'status',
        'logged_in_at',
        'notes',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    /**
     * Currently assigned extension.
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(PbxExtension::class, 'current_extension_id');
    }

    /**
     * Assign employee extension to this physical hot desk station.
     */
    public function assignExtension(PbxExtension $extension): bool
    {
        // First check if this extension was logged in anywhere else, release it
        static::where('current_extension_id', $extension->id)
            ->where('id', '!=', $this->id)
            ->update([
                'current_extension_id' => null,
                'current_extension'    => null,
                'current_user_name'    => null,
                'status'               => 'available',
                'logged_in_at'         => null,
            ]);

        $userName = $extension->user?->name ?: $extension->name;

        return $this->update([
            'current_extension_id' => $extension->id,
            'current_extension'    => $extension->extension,
            'current_user_name'    => $userName,
            'status'               => 'occupied',
            'logged_in_at'         => now(),
        ]);
    }

    /**
     * Release this physical hot desk station back to available pool.
     */
    public function releaseExtension(): bool
    {
        return $this->update([
            'current_extension_id' => null,
            'current_extension'    => null,
            'current_user_name'    => null,
            'status'               => 'available',
            'logged_in_at'         => null,
        ]);
    }
}
