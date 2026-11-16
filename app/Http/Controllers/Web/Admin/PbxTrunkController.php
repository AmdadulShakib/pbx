<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxTrunk;
use App\Services\Pbx\PbxRoutingSyncService;
use Illuminate\Http\Request;

class PbxTrunkController extends Controller
{
    protected PbxRoutingSyncService $syncService;

    public function __construct(PbxRoutingSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function index()
    {
        $trunks = PbxTrunk::withCount(['outboundRoutesAsPrimary', 'outboundRoutesAsFailover'])->latest()->get();
        $snippet = $this->syncService->generateTrunksSnippet();

        return view('admin.pbx.trunks.index', compact('trunks', 'snippet'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'type'                => ['required', 'string', 'in:pjsip_registration,pjsip_ip,sip'],
            'host'                => ['required', 'string', 'max:150'],
            'port'                => ['required', 'integer', 'min:1', 'max:65535'],
            'transport'           => ['required', 'string', 'in:udp,tcp,tls'],
            'username'            => ['nullable', 'string', 'max:100'],
            'secret'              => ['nullable', 'string', 'max:255'],
            'auth_user'           => ['nullable', 'string', 'max:100'],
            'outbound_caller_id'  => ['nullable', 'string', 'max:50'],
            'caller_name'         => ['nullable', 'string', 'max:100'],
            'max_channels'        => ['nullable', 'integer', 'min:1', 'max:500'],
            'codecs'              => ['nullable', 'string', 'max:100'],
            'is_registration'     => ['sometimes', 'boolean'],
            'is_active'           => ['sometimes', 'boolean'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_registration'] = $request->boolean('is_registration', true);
        $validated['is_active']       = $request->boolean('is_active', true);
        $validated['codecs']          = $validated['codecs'] ?: 'ulaw,alaw,g729,opus';
        $validated['max_channels']    = $validated['max_channels'] ?: 10;

        PbxTrunk::create($validated);

        return back()->with('ok', __('SIP Trunk created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $trunk = PbxTrunk::findOrFail($id);

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'type'                => ['required', 'string', 'in:pjsip_registration,pjsip_ip,sip'],
            'host'                => ['required', 'string', 'max:150'],
            'port'                => ['required', 'integer', 'min:1', 'max:65535'],
            'transport'           => ['required', 'string', 'in:udp,tcp,tls'],
            'username'            => ['nullable', 'string', 'max:100'],
            'secret'              => ['nullable', 'string', 'max:255'],
            'auth_user'           => ['nullable', 'string', 'max:100'],
            'outbound_caller_id'  => ['nullable', 'string', 'max:50'],
            'caller_name'         => ['nullable', 'string', 'max:100'],
            'max_channels'        => ['nullable', 'integer', 'min:1', 'max:500'],
            'codecs'              => ['nullable', 'string', 'max:100'],
            'is_registration'     => ['sometimes', 'boolean'],
            'is_active'           => ['sometimes', 'boolean'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_registration'] = $request->boolean('is_registration', false);
        $validated['is_active']       = $request->boolean('is_active', false);

        // Keep existing secret if left empty on update
        if (empty($validated['secret'])) {
            unset($validated['secret']);
        }

        $trunk->update($validated);

        return back()->with('ok', __('SIP Trunk updated successfully.'));
    }

    public function destroy($id)
    {
        $trunk = PbxTrunk::findOrFail($id);
        $trunk->delete();

        return back()->with('ok', __('SIP Trunk removed successfully.'));
    }

    public function sync()
    {
        $result = $this->syncService->syncAll();

        return back()
            ->with('ok', __('Trunks configuration compiled and synced with Asterisk.'))
            ->with('trunks_snippet', $result['trunks_snippet']);
    }
}
