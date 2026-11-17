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

        $currentHost = request()->getHost();
        $domain = $currentHost ?: config('pbx.domain');
        $scheme = request()->secure() ? 'wss://' : 'ws://';
        $wsUrl  = $scheme . $domain . '/ws';

        return response()->json([
            'assigned'  => true,
            'extension' => $ext->extension,
            'name'      => $ext->name,
            'password'  => $ext->password,
            'domain'    => $domain,
            'ws_url'    => $wsUrl,
            'contacts'  => $contacts,
        ])->header('Cache-Control', 'no-store');
    }

    public function history()
    {
        $ext = $this->myExtension();
        $user = auth('web')->user() ?: auth('admin')->user() ?: auth()->user();
        $query = \App\Models\PbxCallLog::query()->latest();

        if ($ext) {
            $query->where(function ($q) use ($ext) {
                $q->where('caller', $ext->extension)
                  ->orWhere('callee', $ext->extension);
            });
        } elseif ($user) {
            $query->where('user_id', $user->id);
        }

        $logs = $query->paginate(15);

        return view('user.pbx.history', compact('ext', 'logs', 'user'));
    }
}

