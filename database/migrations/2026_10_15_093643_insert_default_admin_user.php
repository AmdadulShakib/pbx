<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $data = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'type')) {
            $data['type'] = 'admin';
        }
        if (Schema::hasColumn('users', 'user_type')) {
            $data['user_type'] = 'admin';
        }
        if (Schema::hasColumn('users', 'role')) {
            $data['role'] = 'admin';
        }
        if (Schema::hasColumn('users', 'remember_token')) {
            $data['remember_token'] = Str::random(40);
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@admin.com'],
            $data
        );

        if (Schema::hasTable('roles') && Schema::hasTable('role_user')) {
            $roleId = DB::table('roles')->where('slug', 'super-admin')->value('id');
            if (!$roleId) {
                $roleId = DB::table('roles')->insertGetId([
                    'name' => 'Super Admin',
                    'slug' => 'super-admin',
                    'description' => 'Full access to all admin features.',
                    'is_super' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $userId = DB::table('users')->where('email', 'admin@admin.com')->value('id');
            if ($userId && $roleId) {
                $exists = DB::table('role_user')
                    ->where('role_id', $roleId)
                    ->where('user_id', $userId)
                    ->exists();
                if (!$exists) {
                    DB::table('role_user')->insert([
                        'role_id' => $roleId,
                        'user_id' => $userId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'admin@admin.com')->delete();
    }
};
