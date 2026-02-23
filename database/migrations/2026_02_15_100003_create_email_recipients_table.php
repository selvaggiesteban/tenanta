<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Recipient info (denormalized for historical record)
            $table->string('email');
            $table->string('name')->nullable();

            // Personalization data
            $table->json('merge_fields')->nullable();

            // Status tracking
            $table->string('status')->default('pending'); // pending, sent, delivered, opened, clicked, bounced, failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            // Tracking counts
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);

            // Error handling
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // External references
            $table->string('message_id')->nullable(); // Provider message ID
            $table->string('provider')->nullable(); // smtp, ses, sendgrid, etc.

            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['campaign_id', 'email']);
            $table->index('user_id');
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_recipients');
    }
};
