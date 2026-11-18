<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxConference;
use App\Services\Pbx\AsteriskAmi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PbxConferenceController extends Controller
{
    /**
     * Display a listing of team bridge conference rooms and live participant status.
     */
    public function index(AsteriskAmi $ami): View
    {
        $rooms = PbxConference::orderBy('room_number', 'asc')->get();

        // Query active conference participants from Asterisk
        $liveConferences = [];
        try {
            $cmdResp = $ami->command('confbridge list');
            if (!empty($cmdResp)) {
                $lines = explode("\n", $cmdResp);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, 'Conference') || str_starts_with($line, '===')) {
                        continue;
                    }
                    $parts = preg_split('/\s+/', $line);
                    if (count($parts) >= 2) {
                        $confName = $parts[0];
                        $usersCount = intval($parts[1] ?? 0);
                        $locked = strtolower($parts[3] ?? '') === 'yes';
                        $muted = strtolower($parts[4] ?? '') === 'yes';
                        $liveConferences[$confName] = [
                            'users'  => $usersCount,
                            'locked' => $locked,
                            'muted'  => $muted,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Asterisk unreachable or no conferences active
        }

        $totalRooms = $rooms->count();
        $activeRooms = 0;
        $totalLiveUsers = 0;

        foreach ($rooms as $room) {
            $roomLive = $liveConferences[$room->room_number] ?? null;
            $room->live_users = $roomLive ? $roomLive['users'] : 0;
            $room->is_locked = $roomLive ? $roomLive['locked'] : false;
            $room->is_muted = $roomLive ? $roomLive['muted'] : false;

            if ($room->live_users > 0) {
                $activeRooms++;
                $totalLiveUsers += $room->live_users;
            }
        }

        return view('admin.pbx.conferences.index', compact(
            'rooms',
            'totalRooms',
            'activeRooms',
            'totalLiveUsers'
        ));
    }

    /**
     * Store a newly created conference room.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_number'      => 'required|string|max:20|unique:pbx_conferences,room_number',
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:255',
            'pin'              => 'nullable|string|max:20',
            'max_participants' => 'nullable|integer|min:2|max:300',
            'record'           => 'nullable|boolean',
            'enabled'          => 'nullable|boolean',
        ]);

        $validated['max_participants'] = $validated['max_participants'] ?: 50;
        $validated['record']           = $request->boolean('record');
        $validated['enabled']          = $request->boolean('enabled', true);

        PbxConference::create($validated);

        return redirect()->route('admin.pbx.conferences.index')
            ->with('success', "Team Bridge Room {$validated['room_number']} created successfully.");
    }

    /**
     * Update an existing conference room.
     */
    public function update(Request $request, PbxConference $conference): RedirectResponse
    {
        $validated = $request->validate([
            'room_number'      => 'required|string|max:20|unique:pbx_conferences,room_number,' . $conference->id,
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:255',
            'pin'              => 'nullable|string|max:20',
            'max_participants' => 'nullable|integer|min:2|max:300',
            'record'           => 'nullable|boolean',
            'enabled'          => 'nullable|boolean',
        ]);

        $validated['max_participants'] = $validated['max_participants'] ?: 50;
        $validated['record']           = $request->boolean('record');
        $validated['enabled']          = $request->boolean('enabled', true);

        $conference->update($validated);

        return redirect()->route('admin.pbx.conferences.index')
            ->with('success', "Team Bridge Room {$conference->room_number} updated successfully.");
    }

    /**
     * Delete a conference room.
     */
    public function destroy(PbxConference $conference): RedirectResponse
    {
        $num = $conference->room_number;
        $conference->delete();

        return redirect()->route('admin.pbx.conferences.index')
            ->with('success', "Team Bridge Room {$num} deleted successfully.");
    }

    /**
     * Live API polling for active participants.
     */
    public function liveStatus(AsteriskAmi $ami): JsonResponse
    {
        $rooms = PbxConference::where('enabled', true)->get();
        $liveData = [];

        try {
            $cmdResp = $ami->command('confbridge list');
            if (!empty($cmdResp)) {
                $lines = explode("\n", $cmdResp);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, 'Conference') || str_starts_with($line, '===')) {
                        continue;
                    }
                    $parts = preg_split('/\s+/', $line);
                    if (count($parts) >= 2) {
                        $confName = $parts[0];
                        $usersCount = intval($parts[1] ?? 0);
                        $locked = strtolower($parts[3] ?? '') === 'yes';
                        $muted = strtolower($parts[4] ?? '') === 'yes';
                        $liveData[$confName] = [
                            'users'  => $usersCount,
                            'locked' => $locked,
                            'muted'  => $muted,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        $result = [];
        foreach ($rooms as $room) {
            $live = $liveData[$room->room_number] ?? null;
            $result[] = [
                'id'          => $room->id,
                'room_number' => $room->room_number,
                'name'        => $room->name,
                'users'       => $live ? $live['users'] : 0,
                'locked'      => $live ? $live['locked'] : false,
                'muted'       => $live ? $live['muted'] : false,
            ];
        }

        return response()->json([
            'success' => true,
            'rooms'   => $result,
        ]);
    }
}
