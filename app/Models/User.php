<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class User extends Authenticatable
{
        use HasApiTokens;

use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'password',
        'type', // 'admin' or 'user'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function ($user) {
            $table = $user->getTable();
            if (Schema::hasColumn($table, 'user_type') && empty($user->user_type)) {
                $user->user_type = 'user';
            } elseif (Schema::hasColumn($table, 'type') && empty($user->type)) {
                $user->type = 'user';
            } elseif (Schema::hasColumn($table, 'role') && empty($user->role)) {
                $user->role = 'user';
            }

            if (Schema::hasColumn($table, 'remember_token') && empty($user->remember_token)) {
                $user->remember_token = Str::random(60);
            }
        });
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasPermission(string $ability): bool
    {
        if (!$this->isAdminAccount()) {
            return false;
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_user') || !Schema::hasTable('role_permission')) {
            return true;
        }

        if (Role::query()->count() === 0) {
            return true;
        }

        if ($this->roles()->where('is_super', true)->exists()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', function ($query) use ($ability) {
            $query->where('key', $ability);
        })->exists();
    }

    protected function isAdminAccount(): bool
    {
        $type = $this->user_type ?? $this->type ?? $this->role ?? null;
        return $type === 'admin';
    }

}
