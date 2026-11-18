<?php

namespace App\Http\Controllers\Web\User;

use App\Models\PbxExtension;
use App\Models\PbxConference;
use App\Services\Pbx\AsteriskAmi;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Shows who joined, who is talking, who is muted, and who is not in the room yet.
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

                    $isMuted = str_contains($flags, 'm');
                    $isAdmin = str_contains($flags, 'A');

                    $activeParticipants[] = [
                        'channel'    => $channel,
                        'extension'  => $matchedExt ?: 'Unknown',
                        'name'       => $name,
                        'avatar'     => $avatar,
                        'is_muted'   => $isMuted,
                        'is_talking' => !$isMuted,
                        'is_admin'   => $isAdmin,
                        'is_me'      => $isMe,
                    ];

                    if ($matchedExt) {
                        $activeExtMap[$matchedExt] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Asterisk query failed or bridge empty
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
     * Mute / Unmute / Kick a participant from the conference room.
     */
    public function conferenceAction(Request $request, string $room, AsteriskAmi $ami): JsonResponse
    {
        $action  = $request->input('action'); // 'mute', 'unmute', 'kick'
        $channel = trim($request->input('channel', ''));

        if (empty($channel)) {
            return response()->json(['success' => false, 'message' => 'Channel is required'], 422);
        }

        try {
            if ($action === 'mute') {
                $ami->command("confbridge mute {$room} {$channel}");
            } elseif ($action === 'unmute') {
                $ami->command("confbridge unmute {$room} {$channel}");
            } elseif ($action === 'kick') {
                $ami->command("confbridge kick {$room} {$channel}");
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

