<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbx_ivrs', function (Blueprint $table) {
            $table->string('audio_file', 255)->nullable()->after('sound_prompt');
        });
    }

    public function down(): void
    {
        Schema::table('pbx_ivrs', function (Blueprint $table) {
            $table->dropColumn('audio_file');
        });
    }
};
