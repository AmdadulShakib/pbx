<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pbx_hot_desks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Sales Pod Station 01"
            $table->string('station_endpoint')->unique(); // e.g. "desk-101" or "station-01"
            $table->string('device_model')->default('Yealink SIP-T46U');
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('location')->nullable(); // e.g. "Floor 2 - Sales Zone"
            $table->foreignId('current_extension_id')->nullable()->constrained('pbx_extensions')->nullOnDelete();
            $table->string('current_extension')->nullable();
            $table->string('current_user_name')->nullable();
            $table->string('pin')->default('1234');
            $table->string('status')->default('available'); // available, occupied, maintenance, offline
            $table->timestamp('logged_in_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbx_hot_desks');
    }
};
