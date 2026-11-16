<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxExtension;
use App\Models\PbxInboundRoute;
use App\Models\PbxIvr;
use App\Models\PbxRingGroup;
use App\Services\Pbx\PbxRoutingSyncService;
use Illuminate\Http\Request;

class PbxInboundRouteController extends Controller
{
    protected PbxRoutingSyncService $syncService;

    public function __construct(PbxRoutingSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function index()
    {
        $routes     = PbxInboundRoute::orderBy('priority')->get();
        $extensions = PbxExtension::orderBy('extension')->get();
        $ivrs       = PbxIvr::where('is_active', true)->orderBy('extension')->get();
        $ringGroups = PbxRingGroup::where('is_active', true)->orderBy('extension')->get();
        $snippet    = $this->syncService->generateDialplanSnippet();

        return view('admin.pbx.inbound_routes.index', compact('routes', 'extensions', 'ivrs', 'ringGroups', 'snippet'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'did_pattern'        => ['required', 'string', 'max:50'],
            'caller_id_pattern'  => ['nullable', 'string', 'max:50'],
            'destination_type'   => ['required', 'string', 'in:extension,ivr,ring_group,hangup'],
            'destination_id'     => ['nullable', 'string', 'max:50'],
            'cid_prefix'         => ['nullable', 'string', 'max:50'],
            'priority'           => ['required', 'integer', 'min:1', 'max:999'],
            'is_active'          => ['sometimes', 'boolean'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $validated['caller_id_pattern'] = $validated['caller_id_pattern'] ?: 'ANY';
        $validated['destination_id']    = $validated['destination_id'] ?: '101';
        $validated['is_active']         = $request->boolean('is_active', true);

        PbxInboundRoute::create($validated);

        return back()->with('ok', __('Inbound Route created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $route = PbxInboundRoute::findOrFail($id);

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'did_pattern'        => ['required', 'string', 'max:50'],
            'caller_id_pattern'  => ['nullable', 'string', 'max:50'],
            'destination_type'   => ['required', 'string', 'in:extension,ivr,ring_group,hangup'],
            'destination_id'     => ['nullable', 'string', 'max:50'],
            'cid_prefix'         => ['nullable', 'string', 'max:50'],
            'priority'           => ['required', 'integer', 'min:1', 'max:999'],
            'is_active'          => ['sometimes', 'boolean'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $validated['caller_id_pattern'] = $validated['caller_id_pattern'] ?: 'ANY';
        $validated['destination_id']    = $validated['destination_id'] ?: '101';
        $validated['is_active']         = $request->boolean('is_active', false);

        $route->update($validated);

        return back()->with('ok', __('Inbound Route updated successfully.'));
    }

    public function destroy($id)
    {
        $route = PbxInboundRoute::findOrFail($id);
        $route->delete();

        return back()->with('ok', __('Inbound Route removed successfully.'));
    }

    public function sync()
    {
        $result = $this->syncService->syncAll();

        return back()
            ->with('ok', __('Inbound DID routing rules compiled and synced with Asterisk.'))
            ->with('dialplan_snippet', $result['dialplan_snippet']);
    }
}
