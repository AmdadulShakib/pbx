<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxExtension;
use App\Models\PbxIvr;
use App\Models\PbxIvrOption;
use App\Services\Pbx\AsteriskAmi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PbxIvrController extends Controller
{
    public function index()
    {
        $ivrs = PbxIvr::with('options')->latest()->get();
        $extensions = PbxExtension::orderBy('extension')->get();

        return view('admin.pbx.ivr', compact('ivrs', 'extensions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'extension'           => ['required', 'digits_between:2,8', 'unique:pbx_ivrs,extension'],
            'description'         => ['nullable', 'string', 'max:500'],
            'greeting_message'    => ['nullable', 'string', 'max:500'],
            'sound_prompt'        => ['nullable', 'string', 'max:100'],
            'timeout'             => ['nullable', 'integer', 'min:3', 'max:60'],
            'timeout_destination' => ['required', 'string', 'max:50'],
            'invalid_destination' => ['required', 'string', 'max:50'],
            'is_active'           => ['sometimes', 'boolean'],
            'digits'              => ['nullable', 'array'],
            'digits.*'            => ['required', 'string', 'max:5'],
            'destinations'        => ['nullable', 'array'],
            'destinations.*'      => ['required', 'string', 'max:50'],
            'labels'              => ['nullable', 'array'],
            'labels.*'            => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $ivr = PbxIvr::create([
                'name'                => $validated['name'],
                'extension'           => $validated['extension'],
                'description'         => $validated['description'] ?? null,
                'greeting_message'    => $validated['greeting_message'] ?? null,
                'sound_prompt'        => $validated['sound_prompt'] ?: 'beep',
                'timeout'             => $validated['timeout'] ?? 10,
                'timeout_destination' => $validated['timeout_destination'],
                'invalid_destination' => $validated['invalid_destination'],
                'is_active'           => $request->boolean('is_active', true),
            ]);

            if (!empty($validated['digits'])) {
                foreach ($validated['digits'] as $index => $digit) {
                    if (trim($digit) === '') continue;
                    $dest  = $validated['destinations'][$index] ?? '101';
                    $label = $validated['labels'][$index] ?? null;

                    PbxIvrOption::create([
                        'ivr_id'           => $ivr->id,
                        'digit'            => trim($digit),
                        'destination_type' => 'extension',
                        'destination'      => trim($dest),
                        'label'            => $label ? trim($label) : null,
                    ]);
                }
            }
        });

        return back()->with('ok', __('IVR Digital Receptionist created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $ivr = PbxIvr::findOrFail($id);

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'extension'           => ['required', 'digits_between:2,8', 'unique:pbx_ivrs,extension,' . $ivr->id],
            'description'         => ['nullable', 'string', 'max:500'],
            'greeting_message'    => ['nullable', 'string', 'max:500'],
            'sound_prompt'        => ['nullable', 'string', 'max:100'],
            'timeout'             => ['nullable', 'integer', 'min:3', 'max:60'],
            'timeout_destination' => ['required', 'string', 'max:50'],
            'invalid_destination' => ['required', 'string', 'max:50'],
            'is_active'           => ['sometimes', 'boolean'],
            'digits'              => ['nullable', 'array'],
            'digits.*'            => ['required', 'string', 'max:5'],
            'destinations'        => ['nullable', 'array'],
            'destinations.*'      => ['required', 'string', 'max:50'],
            'labels'              => ['nullable', 'array'],
            'labels.*'            => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($ivr, $validated, $request) {
            $ivr->update([
                'name'                => $validated['name'],
                'extension'           => $validated['extension'],
                'description'         => $validated['description'] ?? null,
                'greeting_message'    => $validated['greeting_message'] ?? null,
                'sound_prompt'        => $validated['sound_prompt'] ?: 'beep',
                'timeout'             => $validated['timeout'] ?? 10,
                'timeout_destination' => $validated['timeout_destination'],
                'invalid_destination' => $validated['invalid_destination'],
                'is_active'           => $request->boolean('is_active', true),
            ]);

            // Replace options
            $ivr->options()->delete();

            if (!empty($validated['digits'])) {
                foreach ($validated['digits'] as $index => $digit) {
                    if (trim($digit) === '') continue;
                    $dest  = $validated['destinations'][$index] ?? '101';
                    $label = $validated['labels'][$index] ?? null;

                    PbxIvrOption::create([
                        'ivr_id'           => $ivr->id,
                        'digit'            => trim($digit),
                        'destination_type' => 'extension',
                        'destination'      => trim($dest),
                        'label'            => $label ? trim($label) : null,
                    ]);
                }
            }
        });

        return back()->with('ok', __('IVR Digital Receptionist updated successfully.'));
    }

    public function destroy($id)
    {
        $ivr = PbxIvr::findOrFail($id);
        $ivr->delete();

        return back()->with('ok', __('IVR menu deleted successfully.'));
    }

    /**
     * Generate Asterisk dialplan code and sync with Asterisk engine.
     */
    public function syncDialplan(AsteriskAmi $ami)
    {
        $ivrs = PbxIvr::with('options')->where('is_active', true)->get();

        $dialplan = "; ==========================================\n";
        $dialplan .= "; Auto-Generated VoxPulse IVR Dialplans\n";
        $dialplan .= "; Updated: " . now()->toIso8601String() . "\n";
        $dialplan .= "; ==========================================\n\n";

        foreach ($ivrs as $ivr) {
            $ctx = "ivr-{$ivr->extension}";
            $dialplan .= "[{$ctx}]\n";
            $dialplan .= "exten => s,1,NoOp(Starting IVR: {$ivr->name} [Ext {$ivr->extension}])\n";
            $dialplan .= " same => n,Answer()\n";
            $dialplan .= " same => n,Wait(1)\n";
            $dialplan .= " same => n,Set(TIMEOUT(digit)=3)\n";
            $dialplan .= " same => n,Set(TIMEOUT(response)={$ivr->timeout})\n";
            $dialplan .= " same => n,Playback(" . ($ivr->sound_prompt ?: 'beep') . ")\n";
            $dialplan .= " same => n,WaitExten({$ivr->timeout})\n\n";

            foreach ($ivr->options as $opt) {
                $dialplan .= "exten => {$opt->digit},1,NoOp(IVR [{$ivr->extension}] Option {$opt->digit} -> {$opt->destination} [{$opt->label}])\n";
                $dialplan .= " same => n,Goto(internal-calls,{$opt->destination},1)\n";
            }

            // Timeout & Invalid
            $dialplan .= "exten => t,1,NoOp(IVR [{$ivr->extension}] Timeout -> {$ivr->timeout_destination})\n";
            $dialplan .= " same => n,Goto(internal-calls,{$ivr->timeout_destination},1)\n";

            $dialplan .= "exten => i,1,NoOp(IVR [{$ivr->extension}] Invalid -> {$ivr->invalid_destination})\n";
            $dialplan .= " same => n,Goto(internal-calls,{$ivr->invalid_destination},1)\n\n";
        }

        // Trigger Asterisk dialplan reload
        $res = $ami->query([
            'action'  => 'command',
            'command' => 'dialplan reload',
        ]);

        return back()->with('ok', __('IVR Dialplan generated and Asterisk reloaded successfully.'))
            ->with('dialplan_snippet', $dialplan);
    }
}
