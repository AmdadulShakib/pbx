<?php

namespace App\Http\Controllers\Web\User;

use App\Models\PbxExtension;
use App\Http\Controllers\Controller;

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
            ->with('user:id,name,email')
            ->orderBy('extension')
            ->get();

        return view('user.pbx.phone', compact('ext', 'contacts'));
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

        $contacts = PbxExtension::where('enabled', true)
            ->where('id', '!=', $ext->id)
            ->with('user:id,name,email')
            ->orderBy('extension')
            ->get()
            ->map(function ($c) {
                return [
                    'extension' => $c->extension,
                    'name'      => $c->name,
                    'user_name' => $c->user?->name,
                ];
            });

        return response()->json([
            'assigned'  => true,
            'extension' => $ext->extension,
            'name'      => $ext->name,
            'password'  => $ext->password,
            'domain'    => config('pbx.domain'),
            'ws_url'    => config('pbx.ws_url'),
            'contacts'  => $contacts,
        ])->header('Cache-Control', 'no-store');
    }
}

