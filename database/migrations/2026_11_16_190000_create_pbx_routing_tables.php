<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. SIP Trunks
        Schema::create('pbx_trunks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 50)->default('pjsip_registration'); // pjsip_registration, pjsip_ip, sip
            $table->string('host', 150);
            $table->integer('port')->default(5060);
            $table->string('transport', 20)->default('udp'); // udp, tcp, tls
            $table->string('username', 100)->nullable();
            $table->string('secret', 255)->nullable();
            $table->string('auth_user', 100)->nullable();
            $table->string('outbound_caller_id', 50)->nullable();
            $table->string('caller_name', 100)->nullable();
            $table->integer('max_channels')->default(10);
            $table->string('codecs', 100)->default('ulaw,alaw,g729,opus');
            $table->boolean('is_registration')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Ring Groups
        Schema::create('pbx_ring_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('extension', 20)->unique();
            $table->string('strategy', 30)->default('ringall'); // ringall, hunt, random
            $table->integer('ring_time')->default(20);
            $table->json('members')->nullable();
            $table->string('failover_type', 30)->default('extension'); // extension, ivr, voicemail, hangup
            $table->string('failover_destination', 50)->default('101');
            $table->boolean('is_active')->default(true);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // 3. Inbound Routes (DID Routing)
        Schema::create('pbx_inbound_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('did_pattern', 50)->default('ANY');
            $table->string('caller_id_pattern', 50)->default('ANY');
            $table->string('destination_type', 30)->default('extension'); // ivr, extension, ring_group, hangup
            $table->string('destination_id', 50)->default('101');
            $table->string('cid_prefix', 50)->nullable();
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Outbound Routes (Dial Patterns to Trunks)
        Schema::create('pbx_outbound_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('dial_pattern', 100)->default('_X.');
            $table->integer('strip_digits')->default(0);
            $table->string('prepend_digits', 50)->nullable();
            $table->foreignId('primary_trunk_id')->nullable()->constrained('pbx_trunks')->nullOnDelete();
            $table->foreignId('failover_trunk_id')->nullable()->constrained('pbx_trunks')->nullOnDelete();
            $table->string('caller_id_override', 50)->nullable();
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbx_outbound_routes');
        Schema::dropIfExists('pbx_inbound_routes');
        Schema::dropIfExists('pbx_ring_groups');
        Schema::dropIfExists('pbx_trunks');
    }
};
