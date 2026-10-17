<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PbxExtension;
use App\Models\User;
use App\Services\Pbx\FreeSwitchConfig;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PbxExtensionController extends Controller
{
    public function __construct(private FreeSwitchConfig $config) {}

    public function index()
    {
        $extensions = PbxExtension::with('user')->orderBy('extension')->get();

        return view('pbx.extensions.index', compact('extensions'));
    }

    public function create()
    {
        return view('pbx.extensions.form', [
            'extension' => new PbxExtension(),
            'users'     => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        PbxExtension::create($this->validated($request));
        $this->config->sync();

        return redirect()->route('pbx.extensions.index')->with('ok', 'এক্সটেনশন তৈরি হয়েছে');
    }

    public function edit(PbxExtension $extension)
    {
        return view('pbx.extensions.form', [
            'extension' => $extension,
            'users'     => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PbxExtension $extension)
    {
        $extension->update($this->validated($request, $extension));
        $this->config->sync();

        return redirect()->route('pbx.extensions.index')->with('ok', 'আপডেট হয়েছে');
    }

    public function destroy(PbxExtension $extension)
    {
        $extension->delete();
        $this->config->sync();

        return redirect()->route('pbx.extensions.index')->with('ok', 'মুছে ফেলা হয়েছে');
    }

    private function validated(Request $request, ?PbxExtension $ext = null): array
    {
        $id = $ext ? $ext->id : null;

        $data = $request->validate([
            'extension' => [
                'required', 'digits_between:3,6',
                'not_regex:/^10[01][0-9]$/',
                Rule::unique('pbx_extensions', 'extension')->ignore($id),
            ],
            'name'     => ['required', 'string', 'max:50'],
            'password' => [$ext ? 'nullable' : 'required', 'string', 'min:8', 'max:64'],
            'user_id'  => [
                'nullable', 'exists:users,id',
                Rule::unique('pbx_extensions', 'user_id')->ignore($id),
            ],
        ]);

        $data['enabled'] = $request->boolean('enabled');

        if ($ext && empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
