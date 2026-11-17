<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxCallLog extends Model
{
    use HasFactory;

    protected $table = 'pbx_call_logs';

    protected $fillable = [
        'user_id',
        'caller',
        'caller_name',
        'callee',
        'callee_name',
        'answered_by',
        'direction',
        'status',
        'duration',
        'sip_call_id',
        'recording_file',
        'notes',
        'started_at',
        'ended_at',
    ];

    public function getRecordingUrlAttribute(): string
    {
        return route('admin.pbx.calls.recording', $this->id);
    }

    public function hasRecording(): bool
    {
        return !empty($this->recording_file) && $this->status === 'answered' && $this->duration > 0;
    }

    protected $casts = [
        'duration'   => 'integer',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration ?? 0;
        if ($seconds <= 0) {
            return '00:00';
        }
        $minutes = floor($seconds / 60);
        $remSec  = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $remSec);
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }

    public function scopeMissed($query)
    {
        return $query->whereIn('status', ['missed', 'no-answer', 'busy', 'failed']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->today());
    }
}
