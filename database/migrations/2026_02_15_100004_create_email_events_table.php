<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('email_recipients')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();

            // Event type: sent, delivered, opened, clicked, bounced, complained, unsubscribed
            $table->string('event_type');

            // Event details
            $table->string('url')->nullable(); // For click events
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('client_name')->nullable(); // Gmail, Outlook, etc.
            $table->string('client_os')->nullable();

            // Location (from IP)
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            // Provider webhook data
            $table->json('raw_data')->nullable();

            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['campaign_id', 'event_type']);
            $table->index(['recipient_id', 'event_type']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_events');
    }
};
