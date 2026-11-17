<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxExtension;
use App\Models\PbxIvr;
use App\Models\PbxRingGroup;
use App\Services\Pbx\PbxRoutingSyncService;
use Illuminate\Http\Request;

class PbxRingGroupController extends Controller
{
    protected PbxRoutingSyncService $syncService;

    public function __construct(PbxRoutingSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function index()
    {
        $ringGroups = PbxRingGroup::latest()->get();
        $extensions = PbxExtension::orderBy('extension')->get();
        $ivrs       = PbxIvr::where('is_active', true)->orderBy('extension')->get();
        $snippet    = $this->syncService->generateDialplanSnippet();

        return view('admin.pbx.ring_groups.index', compact('ringGroups', 'extensions', 'ivrs', 'snippet'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:100'],
            'extension'            => ['required', 'digits_between:2,8', 'unique:pbx_ring_groups,extension', 'unique:pbx_extensions,extension', 'unique:pbx_ivrs,extension'],
            'strategy'             => ['required', 'string', 'in:ringall,hunt,random'],
            'ring_time'            => ['required', 'integer', 'min:5', 'max:120'],
            'members'              => ['required', 'array', 'min:1'],
            'members.*'            => ['required', 'string'],
            'failover_type'        => ['required', 'string', 'in:extension,ivr,hangup'],
            'failover_destination' => ['nullable', 'string', 'max:50'],
            'is_active'            => ['sometimes', 'boolean'],
            'description'          => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['failover_destination'] = $validated['failover_destination'] ?: '101';

        PbxRingGroup::create($validated);

        try {
            $this->syncService->syncAll();
        } catch (\Throwable $e) {}

        return back()->with('ok', __('Ring Group created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $ringGroup = PbxRingGroup::findOrFail($id);

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:100'],
            'extension'            => ['required', 'digits_between:2,8', 'unique:pbx_ring_groups,extension,' . $ringGroup->id],
            'strategy'             => ['required', 'string', 'in:ringall,hunt,random'],
            'ring_time'            => ['required', 'integer', 'min:5', 'max:120'],
            'members'              => ['required', 'array', 'min:1'],
            'members.*'            => ['required', 'string'],
            'failover_type'        => ['required', 'string', 'in:extension,ivr,hangup'],
            'failover_destination' => ['nullable', 'string', 'max:50'],
            'is_active'            => ['sometimes', 'boolean'],
            'description'          => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['failover_destination'] = $validated['failover_destination'] ?: '101';

        $ringGroup->update($validated);

        try {
            $this->syncService->syncAll();
        } catch (\Throwable $e) {}

        return back()->with('ok', __('Ring Group updated successfully.'));
    }

    public function destroy($id)
    {
        $ringGroup = PbxRingGroup::findOrFail($id);
        $ringGroup->delete();

        try {
            $this->syncService->syncAll();
        } catch (\Throwable $e) {}

        return back()->with('ok', __('Ring Group removed successfully.'));
    }

    public function sync()
    {
        $result = $this->syncService->syncAll();

        return back()
            ->with('ok', __('Ring Group dialplan rules compiled and synced with Asterisk.'))
            ->with('dialplan_snippet', $result['dialplan_snippet']);
    }
}
