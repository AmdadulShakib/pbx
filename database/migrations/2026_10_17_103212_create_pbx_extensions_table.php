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
        Schema::create('pbx_extensions', function (Blueprint $table) {
            $table->id();
            $table->string('extension', 6)->unique();
            $table->string('name');
            $table->text('password');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbx_extensions');
    }
};
