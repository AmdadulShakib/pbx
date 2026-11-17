<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbx_call_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('pbx_call_logs', 'answered_by')) {
                $table->string('answered_by', 100)->nullable()->after('callee_name');
            }
            if (!Schema::hasColumn('pbx_call_logs', 'recording_file')) {
                $table->string('recording_file', 255)->nullable()->after('sip_call_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pbx_call_logs', function (Blueprint $table) {
            if (Schema::hasColumn('pbx_call_logs', 'recording_file')) {
                $table->dropColumn('recording_file');
            }
            if (Schema::hasColumn('pbx_call_logs', 'answered_by')) {
                $table->dropColumn('answered_by');
            }
        });
    }
};
