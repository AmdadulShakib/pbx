<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxExtension;
use App\Models\PbxHotDesk;
use App\Services\Pbx\AsteriskAmi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PbxHotDeskController extends Controller
{
    /**
     * Display a listing of physical hot desk stations and extension mobility status.
     */
    public function index(): View
    {
        $stations = PbxHotDesk::with(['extension.user'])
            ->orderBy('id', 'asc')
            ->get();

        $extensions = PbxExtension::with('user')
            ->where('is_active', true)
            ->orderBy('extension', 'asc')
            ->get();

        $totalStations       = $stations->count();
        $occupiedStations    = $stations->where('status', 'occupied')->count();
        $availableStations   = $stations->where('status', 'available')->count();
        $maintenanceStations = $stations->whereIn('status', ['maintenance', 'offline'])->count();

        return view('admin.pbx.hot_desking.index', compact(
            'stations',
            'extensions',
            'totalStations',
            'occupiedStations',
            'availableStations',
            'maintenanceStations'
        ));
    }

    /**
     * Store a newly created hot desk station.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'station_endpoint' => 'required|string|max:50|unique:pbx_hot_desks,station_endpoint',
            'device_model'     => 'required|string|max:100',
            'mac_address'      => 'nullable|string|max:30',
            'ip_address'       => 'nullable|ip',
            'location'         => 'nullable|string|max:100',
            'pin'              => 'nullable|string|max:10',
            'notes'            => 'nullable|string|max:500',
        ]);

        if (empty($validated['pin'])) {
            $validated['pin'] = '1234';
        }

        $validated['status'] = 'available';

        PbxHotDesk::create($validated);

        return redirect()->route('admin.pbx.hot-desking.index')
            ->with('success', __('Hot Desk station created successfully.'));
    }

    /**
     * Update the specified hot desk station.
     */
    public function update(Request $request, PbxHotDesk $hotDesking): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'station_endpoint' => 'required|string|max:50|unique:pbx_hot_desks,station_endpoint,' . $hotDesking->id,
            'device_model'     => 'required|string|max:100',
            'mac_address'      => 'nullable|string|max:30',
            'ip_address'       => 'nullable|ip',
            'location'         => 'nullable|string|max:100',
            'pin'              => 'nullable|string|max:10',
            'status'           => 'required|in:available,occupied,maintenance,offline',
            'notes'            => 'nullable|string|max:500',
        ]);

        $hotDesking->update($validated);

        return redirect()->route('admin.pbx.hot-desking.index')
            ->with('success', __('Hot Desk station updated successfully.'));
    }

    /**
     * Remove the specified hot desk station.
     */
    public function destroy(PbxHotDesk $hotDesking): RedirectResponse
    {
        // Try clearing AstDB
        $this->syncAstDb($hotDesking->station_endpoint, null);

        $hotDesking->delete();

        return redirect()->route('admin.pbx.hot-desking.index')
            ->with('success', __('Hot Desk station deleted successfully.'));
    }

    /**
     * Assign an employee extension to a physical hot desk station (Login).
     */
    public function assign(Request $request, PbxHotDesk $hotDesk): RedirectResponse
    {
        $request->validate([
            'extension_id' => 'required|exists:pbx_extensions,id',
        ]);

        $extension = PbxExtension::findOrFail($request->input('extension_id'));

        $hotDesk->assignExtension($extension);

        // Sync with Asterisk DB
        $this->syncAstDb($hotDesk->station_endpoint, $extension->extension);

        return redirect()->route('admin.pbx.hot-desking.index')
            ->with('success', __("Ext :ext (:name) logged into :desk successfully.", [
                'ext'  => $extension->extension,
                'name' => $extension->user?->name ?: $extension->name,
                'desk' => $hotDesk->name,
            ]));
    }

    /**
     * Release extension from physical hot desk station (Logout).
     */
    public function release(PbxHotDesk $hotDesk): RedirectResponse
    {
        $prevExt = $hotDesk->current_extension;
        $hotDesk->releaseExtension();

        // Remove from Asterisk DB
        $this->syncAstDb($hotDesk->station_endpoint, null);

        return redirect()->route('admin.pbx.hot-desking.index')
            ->with('success', __("Station :desk released (Ext :ext logged out).", [
                'desk' => $hotDesk->name,
                'ext'  => $prevExt ?: 'N/A',
            ]));
    }

    /**
     * API Status for Softphones, IP phones, or Dialplan AGI.
     */
    public function apiStatus(Request $request): JsonResponse
    {
        $endpoint = $request->query('endpoint');

        if ($endpoint) {
            $station = PbxHotDesk::where('station_endpoint', $endpoint)->first();
            if (!$station) {
                return response()->json(['error' => 'Station not found'], 404);
            }
            return response()->json([
                'success'           => true,
                'endpoint'          => $station->station_endpoint,
                'station_name'      => $station->name,
                'status'            => $station->status,
                'current_extension' => $station->current_extension,
                'current_user_name' => $station->current_user_name,
                'logged_in_at'      => $station->logged_in_at?->toIso8601String(),
            ]);
        }

        $stations = PbxHotDesk::all();
        return response()->json([
            'success'  => true,
            'total'    => $stations->count(),
            'stations' => $stations,
        ]);
    }

    /**
     * Helper to update Asterisk Database (AstDB) via AMI Command.
     */
    protected function syncAstDb(string $endpoint, ?string $extension): void
    {
        try {
            $ami = new AsteriskAmi();
            if ($extension) {
                $ami->sendCommand("database put HOTDESK {$endpoint} {$extension}");
            } else {
                $ami->sendCommand("database del HOTDESK {$endpoint}");
            }
        } catch (\Throwable $e) {
            // Log or ignore if AMI not reachable
        }
    }
}
