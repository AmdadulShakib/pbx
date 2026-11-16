<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbx_ivrs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('extension', 20)->unique();
            $table->text('description')->nullable();
            $table->text('greeting_message')->nullable();
            $table->string('sound_prompt', 100)->nullable()->default('beep');
            $table->integer('timeout')->default(10);
            $table->string('timeout_destination', 50)->default('101');
            $table->string('invalid_destination', 50)->default('101');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pbx_ivr_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ivr_id')->constrained('pbx_ivrs')->cascadeOnDelete();
            $table->string('digit', 5);
            $table->string('destination_type', 30)->default('extension');
            $table->string('destination', 50);
            $table->string('label', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbx_ivr_options');
        Schema::dropIfExists('pbx_ivrs');
    }
};
