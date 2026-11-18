<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pbx_conferences')) {
            Schema::create('pbx_conferences', function (Blueprint $table) {
                $table->id();
                $table->string('room_number', 20)->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('pin', 20)->nullable();
                $table->integer('max_participants')->default(50);
                $table->boolean('record')->default(false);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });

            // Seed default initial team bridge conference rooms
            DB::table('pbx_conferences')->insert([
                [
                    'room_number'      => '700',
                    'name'             => 'Company All-Hands Bridge',
                    'description'      => 'Open team voice room for whole-company sync and multi-party discussions.',
                    'pin'              => null,
                    'max_participants' => 100,
                    'record'           => false,
                    'enabled'          => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'room_number'      => '701',
                    'name'             => 'Executive Boardroom',
                    'description'      => 'Private leadership bridge for executive team meetings and board calls.',
                    'pin'              => '1234',
                    'max_participants' => 20,
                    'record'           => false,
                    'enabled'          => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'room_number'      => '702',
                    'name'             => 'Support & Operations War Room',
                    'description'      => 'Incident response and cross-functional support huddle bridge.',
                    'pin'              => null,
                    'max_participants' => 30,
                    'record'           => false,
                    'enabled'          => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbx_conferences');
    }
};
