<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $completion = $this->profileCompletion($user);

        return view('user.dashboard', [
            'user' => $user,
            'completion' => $completion,
            'meta' => [
                'member_since' => $user?->created_at?->format('M d, Y') ?? 'Not available',
                'last_updated' => $user?->updated_at?->diffForHumans() ?? 'Not available',
            ],
        ]);
    }

    protected function profileCompletion($user): int
    {
        if (!$user) {
            return 0;
        }

        $table = $user->getTable();
        $fields = ['name', 'email', 'phone', 'address', 'avatar'];
        $total = 0;
        $filled = 0;
        foreach ($fields as $field) {
            if (!Schema::hasColumn($table, $field)) {
                continue;
            }
            $total++;
            if (!empty($user->{$field})) {
                $filled++;
            }
        }

        if ($total === 0) {
            return 100;
        }

        return (int) round(($filled / $total) * 100);
    }
}
