<?php

namespace App\Http\Controllers\Web\User;

use App\Models\PbxExtension;
use App\Http\Controllers\Controller;

class PbxPhoneController extends Controller
{
    private function myExtension(): ?PbxExtension
    {
        return PbxExtension::where('user_id', auth()->id())->where('enabled', true)->first();
    }

    public function index()
    {
        return view('pbx.phone', ['ext' => $this->myExtension()]);
    }

    public function credentials()
    {
        $ext = $this->myExtension();
        abort_unless($ext, 404);

        return response()->json([
            'extension' => $ext->extension,
            'name'      => $ext->name,
            'password'  => $ext->password,
            'domain'    => config('pbx.domain'),
            'ws_url'    => config('pbx.ws_url'),
        ])->header('Cache-Control', 'no-store');
    }
}
