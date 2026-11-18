<?php

namespace App\Http\Controllers\Web\User;

use App\Models\PbxExtension;
use App\Models\PbxConference;
use App\Services\Pbx\AsteriskAmi;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PbxPhoneController extends Controller
{
    private function myExtension(): ?PbxExtension
    {
        $userId = auth('web')->id() ?: auth('admin')->id() ?: auth()->id();
        if (!$userId) {
            return null;
        }

        return PbxExtension::where('user_id', $userId)->where('enabled', true)->first();
    }

    public function index()
    {
        $ext = $this->myExtension();
        $contacts = PbxExtension::where('enabled', true)
            ->when($ext, fn($q) => $q->where('id', '!=', $ext->id))
            ->with('user:id,name,email,avatar')
            ->orderBy('extension')
            ->get();

        return view('user.pbx.phone', compact('ext', 'contacts'));
    }

    public function window()
    {
        $ext = $this->myExtension();
        $contacts = PbxExtension::where('enabled', true)
            ->when($ext, fn($q) => $q->where('id', '!=', $ext->id))
            ->with('user:id,name,email,avatar')
            ->orderBy('extension')
            ->get();

        return view('pbx.phone_window', compact('ext', 'contacts'));
    }

    public function credentials()
    {
        $ext = $this->myExtension();
        if (!$ext) {
            return response()->json([
                'assigned' => false,
                'message'  => 'No extension assigned to your account.',
            ]);
        }

        $ext->load('user:id,name,email,avatar');

        $contacts = PbxExtension::where('enabled', true)
            ->where('id', '!=', $ext->id)
            ->with('user:id,name,email,avatar')
            ->orderBy('extension')
            ->get()
            ->map(function ($c) {
                return [
                    'extension' => $c->extension,
                    'name'      => $c->name,
                    'user_name' => $c->user?->name,
                    'avatar'    => $c->user?->avatar_url,
                ];
            });

        // Team Bridge Conference Rooms (Multi-Party Asterisk ConfBridge)
        $conferenceRooms = [];
        try {
            $conferenceRooms = \App\Models\PbxConference::where('enabled', true)
                ->orderBy('room_number', 'asc')
                ->get()
                ->map(function ($room) {
                    return [
                        'extension'     => (string) $room->room_number,
                        'name'          => $room->name,
                        'user_name'     => 'Team Bridge Room ' . $room->room_number,
                        'avatar'        => null,
                        'is_conference' => true,
                        'pin'           => $room->pin,
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {}

        if (empty($conferenceRooms)) {
            $conferenceRooms = [
                [
                    'extension'     => '700',
                    'name'          => 'Company All-Hands Bridge',
                    'user_name'     => 'Team Bridge Room 700',
                    'avatar'        => null,
                    'is_conference' => true,
                    'pin'           => null,
                ],
                [
                    'extension'     => '701',
                    'name'          => 'Executive Boardroom',
                    'user_name'     => 'Leadership Bridge 701',
                    'avatar'        => null,
                    'is_conference' => true,
                    'pin'           => '1234',
                ],
                [
                    'extension'     => '702',
                    'name'          => 'Support & Operations Bridge',
                    'user_name'     => 'War Room 702',
                    'avatar'        => null,
                    'is_conference' => true,
                    'pin'           => null,
                ],
            ];
        }

        $currentHost = request()->getHost();
        $domain = $currentHost ?: config('pbx.domain');
        $scheme = request()->secure() ? 'wss://' : 'ws://';
        $wsUrl  = $scheme . $domain . '/ws';

        return response()->json([
            'assigned'    => true,
            'extension'   => $ext->extension,
            'name'        => $ext->name,
            'user_name'   => $ext->user?->name,
            'avatar'      => $ext->user?->avatar_url,
            'password'    => $ext->password,
            'domain'      => $domain,
            'ws_url'      => $wsUrl,
            'contacts'    => $contacts,
            'conferences' => $conferenceRooms,
        ])->header('Cache-Control', 'no-store');
    }

    public function history()
    {
        $ext = $this->myExtension();
        $user = auth('web')->user() ?: auth('admin')->user() ?: auth()->user();
        $query = \App\Models\PbxCallLog::query()->latest();

        if ($ext) {
            $query->where(function ($q) use ($ext) {
                $q->where('caller', $ext->extension)
                  ->orWhere('callee', $ext->extension);
            });
        } elseif ($user) {
            $query->where('user_id', $user->id);
        }

        $logs = $query->paginate(15);

        return view('user.pbx.history', compact('ext', 'logs', 'user'));
    }

    /**
     * Real-time participant status of a conference room.
     * Shows who joined, who is talking, who is muted, who is on hold, who invited whom, and who is not in the room yet.
     */
    public function conferenceStatus(string $room, AsteriskAmi $ami): JsonResponse
    {
        $room = trim($room);
        $confRecord = PbxConference::where('room_number', $room)->first();
        $roomName   = $confRecord ? $confRecord->name : ("Team Bridge Room {$room}");

        $allExtensions = PbxExtension::with('user:id,name,email,avatar')
            ->where('enabled', true)
            ->get();

        $activeParticipants = [];
        $activeExtMap = [];

        // Invites cache: who invited whom
        $invitesKey = "pbx_conf_invites_{$room}";
        $invites = Cache::get($invitesKey, []);
        $updatedInvites = false;

        try {
            $cmdLines = $ami->command("confbridge list {$room}");
            if (!empty($cmdLines)) {
                foreach ($cmdLines as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, 'Channel') || str_starts_with($line, '===') || str_contains($line, 'No conference bridge')) {
                        continue;
                    }

                    // Extract channel
                    $parts = preg_split('/\s+/', $line);
                    $channel = $parts[0] ?? '';
                    $flags   = $parts[1] ?? '';

                    // Try to identify extension number from line
                    $matchedExt = null;
                    if (preg_match('/<(\d+)>/', $line, $m)) {
                        $matchedExt = $m[1];
                    } elseif (preg_match('/PJSIP\/(\d+)/i', $channel, $m)) {
                        $matchedExt = $m[1];
                    } elseif (preg_match('/Local\/(\d+)/i', $channel, $m)) {
                        $matchedExt = $m[1];
                    }

                    $extObj = $matchedExt ? $allExtensions->firstWhere('extension', $matchedExt) : null;
                    $name   = $extObj ? ($extObj->name ?: $extObj->user?->name) : ($matchedExt ? "Ext {$matchedExt}" : 'Participant');
                    $avatar = $extObj ? $extObj->user?->avatar_url : null;

                    $currentUserId = auth('admin')->id() ?: auth('web')->id();
                    $isMe = $extObj && $extObj->user_id && ($extObj->user_id == $currentUserId);

                    // Check if invited by someone
                    $invitedBy = null;
                    if ($matchedExt && isset($invites[$matchedExt])) {
                        $invitedBy = $invites[$matchedExt]['inviter_name'] ?? null;
                        if (($invites[$matchedExt]['status'] ?? '') !== 'joined') {
                            $invites[$matchedExt]['status'] = 'joined';
                            $updatedInvites = true;
                        }
                    }

                    // Check cached state for mute/hold
                    $cachedState = $matchedExt ? Cache::get("pbx_conf_user_state_{$room}_{$matchedExt}") : null;
                    $isHold = ($cachedState === 'hold');
                    $isMuted = $isHold || ($cachedState === 'muted') || str_contains($flags, 'm');
                    $isTalking = !$isMuted && !$isHold;
                    $isAdmin = str_contains($flags, 'A');

                    $statusLabel = 'Talking';
                    if ($isHold) {
                        $statusLabel = 'On Hold';
                    } elseif ($isMuted) {
                        $statusLabel = 'Muted';
                    }

                    $activeParticipants[] = [
                        'channel'      => $channel,
                        'extension'    => $matchedExt ?: 'Unknown',
                        'name'         => $name,
                        'avatar'       => $avatar,
                        'is_muted'     => $isMuted,
                        'is_hold'      => $isHold,
                        'is_talking'   => $isTalking,
                        'status_label' => $statusLabel,
                        'is_admin'     => $isAdmin,
                        'is_me'        => $isMe,
                        'invited_by'   => $invitedBy,
                    ];

                    if ($matchedExt) {
                        $activeExtMap[$matchedExt] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Asterisk query failed or bridge empty
        }

        if ($updatedInvites) {
            Cache::put($invitesKey, $invites, now()->addMinutes(30));
        }

        // Query Asterisk active channels to check if ringing invitations are still alive
        $asteriskChannels = [];
        try {
            $chanLines = $ami->command("core show channels concise");
            if (!empty($chanLines)) {
                foreach ($chanLines as $cline) {
                    if (preg_match('/^([^\s!]+)/', trim($cline), $cm)) {
                        $asteriskChannels[] = $cm[1];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Pending invites (ringing in last 45 seconds and still actively ringing in Asterisk)
        $pendingInvites = [];
        $now = now()->timestamp;
        foreach ($invites as $ext => $inv) {
            if (($inv['status'] ?? '') === 'ringing' && !isset($activeExtMap[$ext])) {
                $elapsed = $now - ($inv['timestamp'] ?? 0);
                if ($elapsed > 45) {
                    unset($invites[$ext]);
                    $updatedInvites = true;
                    continue;
                }

                // If at least 3 seconds have passed since originate, check if channel is still active in Asterisk
                if ($elapsed >= 3 && !empty($asteriskChannels)) {
                    $hasActiveChan = false;
                    foreach ($asteriskChannels as $achan) {
                        if (str_contains($achan, "PJSIP/{$ext}-") || str_contains($achan, "Local/{$ext}@")) {
                            $hasActiveChan = true;
                            break;
                        }
                    }
                    if (!$hasActiveChan) {
                        // Call was rejected by recipient or timed out in Asterisk!
                        unset($invites[$ext]);
                        $updatedInvites = true;
                        continue;
                    }
                }

                $pendingInvites[] = $inv;
            }
        }

        if ($updatedInvites) {
            Cache::put($invitesKey, $invites, now()->addMinutes(30));
        }

        // Calculate colleagues who have not joined yet
        $offlineMembers = [];
        foreach ($allExtensions as $ext) {
            if (!isset($activeExtMap[$ext->extension])) {
                $offlineMembers[] = [
                    'extension' => (string) $ext->extension,
                    'name'      => $ext->name ?: $ext->user?->name ?: ("Ext " . $ext->extension),
                    'avatar'    => $ext->user?->avatar_url,
                ];
            }
        }

        return response()->json([
            'success'         => true,
            'room'            => $room,
            'room_name'       => $roomName,
            'active_count'    => count($activeParticipants),
            'participants'    => $activeParticipants,
            'offline_members' => $offlineMembers,
            'pending_invites' => $pendingInvites,
        ]);
    }

    /**
     * Ring/Invite a colleague into an active conference room.
     */
    public function conferenceInvite(Request $request, string $room, AsteriskAmi $ami): JsonResponse
    {
        $targetExt = trim($request->input('extension', ''));
        if (empty($targetExt)) {
            return response()->json(['success' => false, 'message' => 'Extension is required'], 422);
        }

        // Identify inviter
        $myExtObj = $this->myExtension();
        $inviterUser = auth('admin')->user() ?: auth('web')->user();
        $inviterName = $myExtObj?->name ?: ($inviterUser?->name ?: 'Colleague');
        $inviterExt  = $myExtObj?->extension ?: 'Admin';

        // Target name & clean short names (strip extra titles in parentheses for clean UI)
        $targetObj = PbxExtension::with('user:id,name')->where('extension', $targetExt)->first();
        $targetName = $targetObj ? ($targetObj->name ?: $targetObj->user?->name) : "Ext {$targetExt}";
        $targetShort = trim(preg_replace('/\s*\(.*?\)/', '', $targetName));
        $inviterShort = trim(preg_replace('/\s*\(.*?\)/', '', $inviterName));

        // Save invite in cache for 30 minutes
        $cacheKey = "pbx_conf_invites_{$room}";
        $invites = Cache::get($cacheKey, []);
        $invites[$targetExt] = [
            'target_ext'         => (string) $targetExt,
            'target_name'        => $targetName,
            'target_short_name'  => $targetShort ?: "Ext {$targetExt}",
            'inviter_name'       => $inviterName,
            'inviter_short_name' => $inviterShort ?: "Ext {$inviterExt}",
            'inviter_ext'        => (string) $inviterExt,
            'status'             => 'ringing', // 'ringing', 'joined'
            'timestamp'          => now()->timestamp,
        ];
        Cache::put($cacheKey, $invites, now()->addMinutes(30));

        try {
            $ami->query([
                'action'   => 'originate',
                'channel'  => "Local/{$targetExt}@rg-member-dial",
                'context'  => 'internal-calls',
                'exten'    => $room,
                'priority' => 1,
                'callerid' => "Team Bridge {$room} <{$room}>",
                'timeout'  => 30000,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Ringing Ext {$targetExt} to join Team Bridge Room {$room}...",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate invite call: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an active ringing invite and terminate the Asterisk ringing channel immediately.
     */
    public function conferenceCancelInvite(Request $request, string $room, AsteriskAmi $ami): JsonResponse
    {
        $targetExt = trim($request->input('extension', ''));
        if (empty($targetExt)) {
            return response()->json(['success' => false, 'message' => 'Extension is required'], 422);
        }

        // 1. Remove from cache immediately
        $cacheKey = "pbx_conf_invites_{$room}";
        $invites = Cache::get($cacheKey, []);
        if (isset($invites[$targetExt])) {
            unset($invites[$targetExt]);
            Cache::put($cacheKey, $invites, now()->addMinutes(30));
        }

        // 2. Hang up all ringing channels for this extension in Asterisk
        try {
            $chanLines = $ami->command("core show channels concise");
            if (!empty($chanLines)) {
                foreach ($chanLines as $cline) {
                    if (preg_match('/^([^\s!]+)/', trim($cline), $cm)) {
                        $chanName = $cm[1];
                        if (str_contains($chanName, "PJSIP/{$targetExt}-") || str_contains($chanName, "Local/{$targetExt}@")) {
                            try {
                                $ami->command("channel request hangup {$chanName}");
                            } catch (\Throwable $e) {}
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Invitation to Ext {$targetExt} cancelled successfully.",
        ]);
    }

    /**
     * Mute / Unmute / Hold / Unhold / Kick a participant from the conference room.
     */
    public function conferenceAction(Request $request, string $room, AsteriskAmi $ami): JsonResponse
    {
        $action    = $request->input('action'); // 'mute', 'unmute', 'hold', 'unhold', 'kick'
        $channel   = trim($request->input('channel', ''));
        $extension = trim($request->input('extension', ''));

        if (empty($channel) && empty($extension)) {
            return response()->json(['success' => false, 'message' => 'Channel or extension is required'], 422);
        }

        if (!$extension && preg_match('/(?:PJSIP|Local)\/(\d+)/i', $channel, $m)) {
            $extension = $m[1];
        }

        try {
            if ($action === 'mute') {
                if ($channel) {
                    try { $ami->command("confbridge mute {$room} {$channel}"); } catch (\Throwable $e) {}
                }
                if ($extension) Cache::put("pbx_conf_user_state_{$room}_{$extension}", 'muted', now()->addHours(2));
            } elseif ($action === 'unmute') {
                if ($channel) {
                    try { $ami->command("confbridge unmute {$room} {$channel}"); } catch (\Throwable $e) {}
                }
                if ($extension) Cache::put("pbx_conf_user_state_{$room}_{$extension}", 'talking', now()->addHours(2));
            } elseif ($action === 'hold') {
                if ($extension) Cache::put("pbx_conf_user_state_{$room}_{$extension}", 'hold', now()->addHours(2));
            } elseif ($action === 'unhold') {
                if ($extension) Cache::put("pbx_conf_user_state_{$room}_{$extension}", 'talking', now()->addHours(2));
            } elseif ($action === 'kick') {
                if ($channel) {
                    try { $ami->command("confbridge kick {$room} {$channel}"); } catch (\Throwable $e) {}
                }
                if ($extension) Cache::forget("pbx_conf_user_state_{$room}_{$extension}");
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

