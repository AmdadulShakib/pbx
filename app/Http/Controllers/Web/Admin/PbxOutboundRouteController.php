<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxOutboundRoute;
use App\Models\PbxTrunk;
use App\Services\Pbx\PbxRoutingSyncService;
use Illuminate\Http\Request;

class PbxOutboundRouteController extends Controller
{
    protected PbxRoutingSyncService $syncService;

    public function __construct(PbxRoutingSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function index()
    {
        $routes  = PbxOutboundRoute::with(['primaryTrunk', 'failoverTrunk'])->orderBy('priority')->get();
        $trunks  = PbxTrunk::where('is_active', true)->orderBy('name')->get();
        $snippet = $this->syncService->generateDialplanSnippet();

        return view('admin.pbx.outbound_routes.index', compact('routes', 'trunks', 'snippet'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'dial_pattern'        => ['required', 'string', 'max:100'],
            'strip_digits'        => ['required', 'integer', 'min:0', 'max:20'],
            'prepend_digits'      => ['nullable', 'string', 'max:50'],
            'primary_trunk_id'    => ['nullable', 'exists:pbx_trunks,id'],
            'failover_trunk_id'   => ['nullable', 'exists:pbx_trunks,id'],
            'caller_id_override'  => ['nullable', 'string', 'max:50'],
            'priority'            => ['required', 'integer', 'min:1', 'max:999'],
            'is_active'           => ['sometimes', 'boolean'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        PbxOutboundRoute::create($validated);

        return back()->with('ok', __('Outbound Route created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $route = PbxOutboundRoute::findOrFail($id);

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'dial_pattern'        => ['required', 'string', 'max:100'],
            'strip_digits'        => ['required', 'integer', 'min:0', 'max:20'],
            'prepend_digits'      => ['nullable', 'string', 'max:50'],
            'primary_trunk_id'    => ['nullable', 'exists:pbx_trunks,id'],
            'failover_trunk_id'   => ['nullable', 'exists:pbx_trunks,id'],
            'caller_id_override'  => ['nullable', 'string', 'max:50'],
            'priority'            => ['required', 'integer', 'min:1', 'max:999'],
            'is_active'           => ['sometimes', 'boolean'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $route->update($validated);

        return back()->with('ok', __('Outbound Route updated successfully.'));
    }

    public function destroy($id)
    {
        $route = PbxOutboundRoute::findOrFail($id);
        $route->delete();

        return back()->with('ok', __('Outbound Route removed successfully.'));
    }

    public function sync()
    {
        $result = $this->syncService->syncAll();

        return back()
            ->with('ok', __('Outbound routing rules compiled and synced with Asterisk.'))
            ->with('dialplan_snippet', $result['dialplan_snippet']);
    }
}
