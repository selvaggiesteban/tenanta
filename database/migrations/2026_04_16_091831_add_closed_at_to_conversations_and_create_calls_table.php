<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('omnichannel_conversations', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('last_message_at');
        });

        Schema::create('omnichannel_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('conversation_id')->nullable()->constrained('omnichannel_conversations')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->string('external_id')->nullable()->index(); // Twilio Call SID
            $table->string('from');
            $table->string('to');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->integer('duration')->nullable(); // in seconds
            $table->string('status')->default('queued'); // Twilio statuses: ringing, in-progress, completed, etc.
            $table->string('recording_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omnichannel_calls');
        Schema::table('omnichannel_conversations', function (Blueprint $table) {
            $table->dropColumn('closed_at');
        });
    }
};
