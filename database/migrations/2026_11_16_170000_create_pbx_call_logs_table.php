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
        Schema::create('pbx_call_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('caller', 50)->index();
            $table->string('caller_name', 100)->nullable();
            $table->string('callee', 50)->index();
            $table->string('callee_name', 100)->nullable();
            $table->string('direction', 20)->default('internal'); // 'inbound', 'outbound', 'internal'
            $table->string('status', 30)->default('answered');   // 'answered', 'missed', 'busy', 'failed', 'rejected'
            $table->unsignedInteger('duration')->default(0);       // Duration in seconds
            $table->string('sip_call_id', 150)->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbx_call_logs');
    }
};
